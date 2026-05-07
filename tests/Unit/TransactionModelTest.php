<?php

use App\Models\Transaction;

test('formatted total divides total_amount_in_mills by 1000 with three decimals', function () {
    $transaction = new Transaction(['total_amount_in_mills' => 2500]);

    expect($transaction->formatted_total)->toBe('2.500');
});

test('formatted change divides change_amount_in_mills by 1000 with three decimals', function () {
    $transaction = new Transaction(['change_amount_in_mills' => 500]);

    expect($transaction->formatted_change)->toBe('0.500');
});

test('formatted total is zero when no amount set', function () {
    $transaction = new Transaction(['total_amount_in_mills' => 0]);

    expect($transaction->formatted_total)->toBe('0.000');
});

test('formatted change is zero when no change given', function () {
    $transaction = new Transaction(['change_amount_in_mills' => 0]);

    expect($transaction->formatted_change)->toBe('0.000');
});
