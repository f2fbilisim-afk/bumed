import { chromium } from "playwright";

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
await page.goto("https://www.instagram.com/bumedofficial/", {
  waitUntil: "networkidle",
  timeout: 90000,
});
await page.waitForTimeout(6000);
const title = await page.title();
const links = await page.locator('a[href*="/p/"]').evaluateAll((els) =>
  els.map((a) => a.href).slice(0, 8)
);
console.log("title:", title);
console.log("links:", links);
await page.screenshot({ path: "/opt/cursor/artifacts/ig-debug.png", fullPage: false });
await browser.close();
