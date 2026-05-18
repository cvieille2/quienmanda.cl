import requests


AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
BASE = "https://infomoteles.cl/wp-json/code-snippets/v1/snippets"


def main():
    page = 1
    active = []

    while True:
        resp = requests.get(f"{BASE}?per_page=100&page={page}", auth=AUTH, verify=False, timeout=60)
        if resp.status_code == 400:
            break
        resp.raise_for_status()
        rows = resp.json()
        if not rows:
            break
        for row in rows:
            if row.get("active"):
                active.append((row["id"], row["name"]))
        page += 1

    print("active snippets:", len(active))
    keys = ["max-width", "container", "entry-content", "motel-shell", "ficha-page", "orbital_layout_container"]

    for sid, name in active:
        detail = requests.get(f"{BASE}/{sid}", auth=AUTH, verify=False, timeout=60)
        detail.raise_for_status()
        code = detail.json().get("code", "")
        marks = [k for k in keys if k in code]
        print(f"{sid}\t{name}\t{'|'.join(marks)}")


if __name__ == "__main__":
    main()
