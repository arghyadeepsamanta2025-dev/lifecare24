LifeCare365 Razorpay integration starter
========================================

This package keeps the existing index.html and adds a Razorpay checkout option plus PHP endpoints.

IMPORTANT: It is NOT ready to accept real payments until you add your own Razorpay credentials and test it.
1. Extract this ZIP into a local PHP server folder (XAMPP: htdocs/lifecare365).
2. Rename config.example.php to config.php.
3. Put your Razorpay TEST Key ID and TEST Key Secret in config.php. Never put the secret in index.html or share it.
4. Start Apache in XAMPP and open http://localhost/lifecare365/ (do not open the file directly).
5. Make sure PHP cURL is enabled. This starter expects PHP sessions and cURL.
6. Use Razorpay Test Mode and test successful, failed, pending and duplicate/retry cases.
7. Only after merchant account approval and full tests should you use live credentials.

Limitations / deployment notes:
- The PDF invoice is generated only after the server verifies signature and fetches the payment from Razorpay and confirms captured status.
- Invoice contains customer name, mobile, service, pickup/billing address, destination, amount, payment ID and order ID. The simple PDF uses ASCII transliteration for non-Latin text.
- This starter stores order/invoice data in PHP session and is for local integration testing, not a complete production ledger. Before live use, add persistent database storage, webhook handling, rate limiting, server-side logging, HTTPS, privacy notice and retention controls.
- Never mark an order PAID based only on a frontend callback.
- InfinityFree may restrict external API calls or webhook reliability. Test compatibility before deployment; if blocked, use a compatible PHP host/backend.
- No API credentials are included in this ZIP. QR-based direct UPI transfers are not automatically verified by this gateway integration.
