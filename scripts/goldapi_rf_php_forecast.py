import os
import json
import requests

def get_gold_price_per_ounce():
    api_key = os.getenv("GOLDAPI_KEY", "YOUR_REAL_KEY_HERE")
    symbol = "XAU"
    curr = "USD"
    url = f"https://www.goldapi.io/api/{symbol}/{curr}"

    headers = {
        "x-access-token": api_key,
        "Content-Type": "application/json",
    }

    USD_TO_PHP = 58.9  # you can move this to env or another API later

    try:
        r = requests.get(url, headers=headers, timeout=10)
        r.raise_for_status()
        data = r.json()

        usd_per_oz = data.get("price")  # <-- THIS is the main field
        if usd_per_oz is None:
            print(json.dumps({
                "error": "missing_price_field",
                "raw": data,
            }))
            return

        php_per_oz = usd_per_oz * USD_TO_PHP

        result = {
            "metal": data.get("metal"),
            "currency": data.get("currency"),
            "usd_per_ounce": round(usd_per_oz, 2),
            "php_per_ounce": round(php_per_oz, 2),
            "usd_to_php": USD_TO_PHP,
            "timestamp": data.get("timestamp"),
            "prev_close_price": data.get("prev_close_price"),
            "open_price": data.get("open_price"),
        }

        print(json.dumps(result))

    except requests.exceptions.RequestException as e:
        print(json.dumps({
            "error": "request_failed",
            "message": str(e),
        }))


if __name__ == "__main__":
    get_gold_price_per_ounce()
