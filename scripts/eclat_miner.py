import sys
import json
from collections import defaultdict

def eclat(prefix, items, min_support, freq_itemsets):
    """
    Recursive ECLAT algorithm.
    items: list of (item, tidset)
    """
    while items:
        item, tidset = items.pop()
        support = len(tidset)

        if support < min_support:
            continue

        new_prefix = prefix + [item]
        freq_itemsets.append({
            "items": new_prefix,
            "support": support,
        })

        # Build new candidate items by intersecting TID-sets
        new_items = []
        for other_item, other_tidset in items:
            inter = tidset & other_tidset
            if len(inter) >= min_support:
                new_items.append((other_item, inter))

        eclat(new_prefix, new_items, min_support, freq_itemsets)


def main():
    raw = sys.stdin.read().strip()
    if not raw:
        print(json.dumps({"frequent_itemsets": []}))
        return

    data = json.loads(raw)
    transactions = data.get("transactions", {})  # { "1": [13,16], ... }
    min_support = int(data.get("min_support", 2))

    # Build vertical representation: item -> set of transaction IDs (TID-set)
    item_tidset = defaultdict(set)

    for tid_str, items in transactions.items():
        tid = int(tid_str)  # or keep as string, but int is fine
        for item in items:
            item_tidset[int(item)].add(tid)

    items_list = list(item_tidset.items())
    freq_itemsets = []

    eclat([], items_list, min_support, freq_itemsets)

    # Optional: sort results (by support desc)
    freq_itemsets.sort(key=lambda x: (-x["support"], x["items"]))

    print(json.dumps({"frequent_itemsets": freq_itemsets}))


if __name__ == "__main__":
    main()
