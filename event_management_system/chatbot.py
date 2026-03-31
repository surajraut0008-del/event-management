from __future__ import annotations

import os
from datetime import datetime

from flask import Flask, jsonify, request, send_file
from flask_cors import CORS

from qr import generate_qr_png

app = Flask(__name__)
CORS(app)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
TICKETS_DIR = os.path.join(BASE_DIR, "tickets")


def normalize(text: str) -> str:
    return (text or "").strip().lower()


@app.get("/health")
def health():
    return jsonify({"ok": True, "time": datetime.utcnow().isoformat() + "Z"})


@app.post("/chat")
def chat():
    data = request.get_json(silent=True) or {}
    msg = normalize(data.get("message", ""))

    if not msg:
        return jsonify({"reply": "Please type a message."})

    if "show events" in msg or "events" == msg:
        # Frontend already lists events; we keep response generic without DB access
        return jsonify(
            {
                "reply": "You can view all events on the Events page. Use the search bar to filter by title/location/price."
            }
        )

    if "how to book" in msg or ("book" in msg and "how" in msg):
        return jsonify(
            {
                "reply": "To book: Login → open an event → click Book → complete Razorpay payment → your QR ticket appears in My Bookings."
            }
        )

    if "payment" in msg:
        return jsonify(
            {
                "reply": "Payments are done using Razorpay Checkout. After success, your booking status becomes PAID."
            }
        )

    return jsonify(
        {
            "reply": "I can help with: 'show events', 'how to book', 'payment'. Try one of these."
        }
    )


def ticket_filename(booking_id: int, user_id: int) -> str:
    return os.path.join(TICKETS_DIR, f"ticket_b{booking_id}_u{user_id}.png")


@app.get("/qr")
def qr_view():
    """
    Generates (or returns existing) QR image for a booking.
    In a real system you'd validate payment status via DB.
    Here we generate based on booking_id+user_id for demo/MP.
    """
    booking_id = int(request.args.get("booking_id", "0") or 0)
    user_id = int(request.args.get("user_id", "0") or 0)
    if booking_id <= 0 or user_id <= 0:
        return jsonify({"error": "Missing booking_id/user_id"}), 400

    payload = f"EventMS Ticket|booking_id={booking_id}|user_id={user_id}"
    out_path = ticket_filename(booking_id, user_id)
    if not os.path.exists(out_path):
        generate_qr_png(payload, out_path)

    return send_file(out_path, mimetype="image/png")


@app.get("/qr/download")
def qr_download():
    booking_id = int(request.args.get("booking_id", "0") or 0)
    user_id = int(request.args.get("user_id", "0") or 0)
    if booking_id <= 0 or user_id <= 0:
        return jsonify({"error": "Missing booking_id/user_id"}), 400

    payload = f"EventMS Ticket|booking_id={booking_id}|user_id={user_id}"
    out_path = ticket_filename(booking_id, user_id)
    if not os.path.exists(out_path):
        generate_qr_png(payload, out_path)

    return send_file(out_path, mimetype="image/png", as_attachment=True, download_name=os.path.basename(out_path))


if __name__ == "__main__":
    # Runs on port 5000 as required
    app.run(host="127.0.0.1", port=5000, debug=True)

