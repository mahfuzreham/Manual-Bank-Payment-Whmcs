# WHMCS Manual Bank Transfer Gateway

A WHMCS payment gateway for accepting manual bank transfers securely. Customers submit their TRX ID, and admins receive a Discord notification for manual verification.

---

## 📂 Category

**Payment Gateway**

---

## 🧩 Integration Developer

**[MD MAHFUZ REHAM](https://github.com/mahfuzreham)**  
GitHub: [https://github.com/mahfuzreham](https://github.com/mahfuzreham)

---

## 💬 Support

For help, suggestions, or issues, open a GitHub issue or contact:  
👉 [https://github.com/mahfuzreham](https://github.com/mahfuzreham)
🛡 License
MIT License — Free to use, modify, and share.

📌 Notes
No third-party payment processors are used.

100% secure and self-hosted.

---

## ✅ Features

- Displays full bank info on the invoice (Bank, Branch, SWIFT, Routing, Account)
- Allows user to submit TRX ID after bank transfer
- Starts a **30-minute verification countdown**
- Sends notification to **Discord webhook**
- Saves data securely to database table (`mod_manualbank_trx`)
- Easy setup through WHMCS admin interface

---

## 🔧 Installation

1. Upload the file to your WHMCS installation:




3. **Configure**:
   - Add your bank details
   - Add your Discord webhook (optional)

---

## 🧪 How It Works

1. Customer views the invoice and sees your bank info.
2. They transfer funds manually and enter the TRX ID.
3. TRX ID is saved and a countdown (30 minutes) starts.
4. You get a notification in your **Discord channel**.
5. You manually verify payment and mark the invoice **Paid**.

---

## 📄 Database Table

The module automatically creates this table:

```sql
CREATE TABLE `mod_manualbank_trx` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `trx_id` varchar(255) NOT NULL,
  `submitted_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);



