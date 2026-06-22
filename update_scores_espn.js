const fs = require("fs");
const https = require("https");
const path = require("path");

const siteDir = __dirname;
const matchesFile = path.join(siteDir, "matches.json");
const dataDir = path.join(siteDir, "data");
const logFile = path.join(dataDir, "score-update-espn.log");
const apiUrl = "https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard?dates=20260611-20260719&limit=200";

function log(message) {
  fs.mkdirSync(dataDir, { recursive: true });
  fs.appendFileSync(logFile, `[${new Date().toISOString()}] ${message}\n`, "utf8");
}

function fetchJson(url) {
  return new Promise((resolve, reject) => {
    https.get(url, { headers: { "User-Agent": "WorldCupH5/1.0" } }, (response) => {
      if (response.statusCode >= 300 && response.statusCode < 400 && response.headers.location) {
        response.resume();
        fetchJson(response.headers.location).then(resolve, reject);
        return;
      }
      if (response.statusCode !== 200) {
        response.resume();
        reject(new Error(`ESPN returned HTTP ${response.statusCode}`));
        return;
      }
      let body = "";
      response.setEncoding("utf8");
      response.on("data", (chunk) => { body += chunk; });
      response.on("end", () => {
        try { resolve(JSON.parse(body)); } catch (error) { reject(error); }
      });
    }).on("error", reject);
  });
}

function singaporeDateTime(value) {
  const parts = Object.fromEntries(new Intl.DateTimeFormat("en-US", {
    timeZone: "Asia/Singapore",
    month: "numeric",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hourCycle: "h23",
  }).formatToParts(new Date(value)).map((part) => [part.type, part.value]));
  return { date: `${parts.month}月${parts.day}日`, time: `${parts.hour}:${parts.minute}` };
}

function stageFor(event) {
  const note = event.competitions?.[0]?.altGameNote || "";
  const group = note.match(/Group\s+([A-L])/i);
  if (group) return group[1].toUpperCase();
  const sourceId = Number(event.id);
  if (sourceId <= 760501) return "32强";
  if (sourceId <= 760509) return "16强";
  if (sourceId <= 760513) return "8强";
  if (sourceId <= 760515) return "半决赛";
  if (sourceId === 760516) return "季军赛";
  return "决赛";
}

function pairKey(home, away) {
  return `${home}-${away}`;
}

function buildMatches(payload, previous) {
  const existingIds = new Map();
  for (const match of previous) {
    const direct = pairKey(match.home, match.away);
    if (!existingIds.has(direct)) existingIds.set(direct, match.id);
  }

  return payload.events.map((event) => {
    const competition = event.competitions?.[0];
    const home = competition?.competitors?.find((team) => team.homeAway === "home");
    const away = competition?.competitors?.find((team) => team.homeAway === "away");
    if (!home || !away) return null;

    const homeCode = home.team.abbreviation;
    const awayCode = away.team.abbreviation;
    const local = singaporeDateTime(event.date);
    const oldId = existingIds.get(pairKey(homeCode, awayCode));
    const done = Boolean(event.status?.type?.completed);
    const match = {
      id: oldId || `espn-${event.id}`,
      sourceId: String(event.id),
      kickoffUtc: event.date,
      group: stageFor(event),
      date: local.date,
      time: local.time,
      home: homeCode,
      away: awayCode,
      homeName: home.team.displayName,
      awayName: away.team.displayName,
      done,
      source: "espn",
    };
    if (done) match.score = [Number(home.score), Number(away.score)];
    return match;
  }).filter(Boolean).sort((a, b) => new Date(a.kickoffUtc) - new Date(b.kickoffUtc));
}

async function main() {
  const previous = fs.existsSync(matchesFile)
    ? JSON.parse(fs.readFileSync(matchesFile, "utf8"))
    : [];
  const payload = await fetchJson(apiUrl);
  if (!Array.isArray(payload.events) || payload.events.length < 72) {
    throw new Error(`Refusing incomplete response: ${payload.events?.length || 0} events`);
  }

  const matches = buildMatches(payload, previous);
  const completed = matches.filter((match) => match.done).length;
  fs.mkdirSync(dataDir, { recursive: true });
  if (fs.existsSync(matchesFile)) {
    const stamp = new Date().toISOString().replace(/[:.]/g, "-");
    fs.copyFileSync(matchesFile, path.join(dataDir, `matches-backup-${stamp}.json`));
  }
  const tempFile = `${matchesFile}.tmp`;
  fs.writeFileSync(tempFile, `${JSON.stringify(matches, null, 2)}\n`, "utf8");
  fs.renameSync(tempFile, matchesFile);
  log(`Updated ${matches.length} matches; completed=${completed}; timezone=Asia/Singapore`);
  console.log(`OK: ${matches.length} matches loaded, ${completed} completed.`);
}

main().catch((error) => {
  log(`ERROR ${error.stack || error.message}`);
  console.error(`Update failed: ${error.message}`);
  process.exit(1);
});
