const TelegramBot = require("node-telegram-bot-api");
const express = require("express");
const bodyParser = require("body-parser");

const BOT_TOKEN = "8445474506:AAERlefbIW3kRQ9c09migGArnlmR1lmJccM";
const PORT = 3000;
const API_SECRET = "kasmini-secret-2026";

const bot = new TelegramBot(BOT_TOKEN, { polling: true });
const app = express();
app.use(bodyParser.json());

bot.onText(/\/start/, (msg) => {
  const nama = msg.from.first_name || "Pelanggan";
  bot.sendMessage(msg.chat.id, "Halo " + nama + "! Selamat datang di Kasmini Laundry Bot. Ketik /help untuk panduan.");
});

bot.onText(/\/help/, (msg) => {
  bot.sendMessage(msg.chat.id, "/start - Mulai bot\n/help - Panduan\n/status - Info layanan\n/chatid - Lihat Chat ID kamu");
});

bot.onText(/\/status/, (msg) => {
  bot.sendMessage(msg.chat.id, "Kasmini Laundry siap melayani! Untuk status pesanan hubungi kasir kami.");
});

bot.onText(/\/chatid/, (msg) => {
  bot.sendMessage(msg.chat.id, "Chat ID kamu: " + msg.chat.id + "\nBerikan ke kasir untuk notifikasi transaksi.");
});

app.post("/send-notification", async (req, res) => {
  const { secret, chat_id, message } = req.body;
  if (secret !== API_SECRET) return res.status(401).json({ success: false, message: "Unauthorized" });
  if (!chat_id || !message) return res.status(400).json({ success: false, message: "chat_id dan message wajib diisi" });
  try {
    await bot.sendMessage(chat_id, message);
    return res.json({ success: true, message: "Notifikasi berhasil dikirim" });
  } catch (err) {
    return res.status(500).json({ success: false, message: err.message });
  }
});

app.listen(PORT, () => {
  console.log("Bot aktif di port " + PORT);
});

