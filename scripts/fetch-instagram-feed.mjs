import { chromium } from "playwright";

const USERNAME = "bumedofficial";
const LIMIT = 3;

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({
  userAgent:
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
});
await page.goto(`https://www.instagram.com/${USERNAME}/`, {
  waitUntil: "domcontentloaded",
  timeout: 60000,
});
await page.waitForTimeout(4000);

const posts = await page.evaluate((limit) => {
  const links = Array.from(document.querySelectorAll(`a[href*="/p/"]`));
  const seen = new Set();
  const out = [];
  for (const link of links) {
    const href = link.getAttribute("href") || "";
    const m = href.match(/\/p\/([^/]+)\//);
    if (!m) continue;
    const shortcode = m[1];
    if (seen.has(shortcode)) continue;
    seen.add(shortcode);
    const img = link.querySelector("img");
    out.push({
      shortcode,
      url: `https://www.instagram.com/p/${shortcode}/`,
      thumbnail: img?.src || "",
      alt: img?.alt || "",
    });
    if (out.length >= limit) break;
  }
  return out;
}, LIMIT);

await browser.close();

const payload = {
  username: USERNAME,
  profileUrl: `https://www.instagram.com/${USERNAME}/`,
  fetchedAt: new Date().toISOString(),
  posts,
};

process.stdout.write(JSON.stringify(payload, null, 2));
