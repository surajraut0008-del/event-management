import os
import qrcode


def ensure_dir(path: str) -> None:
    os.makedirs(path, exist_ok=True)


def generate_qr_png(data: str, out_path: str) -> str:
    """
    Generate a QR code PNG for given data and write it to out_path.
    Returns the final file path.
    """
    img = qrcode.make(data)
    ensure_dir(os.path.dirname(out_path))
    img.save(out_path)
    return out_path

