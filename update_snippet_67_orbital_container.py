import requests


AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
URL = "https://infomoteles.cl/wp-json/code-snippets/v1/snippets/67"


def main():
    current = requests.get(URL, auth=AUTH, verify=False, timeout=60)
    current.raise_for_status()
    data = current.json()
    code = data.get("code", "")

    if "max-width:1200px;" not in code:
        print("No hardcoded 1200px found; nothing to change")
        return

    new_code = code.replace("max-width:1200px;", "max-width:100%;")

    payload = {
        "name": data.get("name", "orbital-menu-restore-inline"),
        "code": new_code,
        "scope": data.get("scope", "global"),
        "active": True,
    }

    resp = requests.put(URL, json=payload, auth=AUTH, verify=False, timeout=60)
    if resp.status_code >= 400:
        resp = requests.post(URL, json=payload, auth=AUTH, verify=False, timeout=60)

    print(resp.status_code)
    print(resp.text[:600])
    resp.raise_for_status()


if __name__ == "__main__":
    main()
