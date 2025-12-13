<?php

function discounted_price(array $p): int {
    return $p['discount_percent'] > 0
        ? (int) ($p['price'] - ($p['price'] * $p['discount_percent'] / 100))
        : (int) $p['price'];
}
