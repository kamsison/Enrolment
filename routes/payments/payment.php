<?php
/**
 * Created by PhpStorm.
 * User: kharl
 * Date: 22/09/2016
 * Time: 4:28 PM
 */
$app->get('/payment/new', $isAuthenticated, function() use ($app) {
    $paymentModes = \um\models\PaymentMode::all();
    $periods = \um\models\Period::all();
    $app->render('payments/index.twig', compact('paymentModes', 'periods'));
})->name('getPayment');

$app->post('/payment', $isAuthenticated, function() use ($app) {
    $payment = new \um\models\Payment();
    $payment->personal_info_id = $app->request->post('student_id');
    $payment->amount = $app->request->post('amount');
    $payment->payment_mode_id = $app->request->post('payment_mode');
    $payment->reference_number = $app->request->post('reference_number');
    $payment->fee_id = $app->request->post('fee_id');
    $payment->period_id = $app->request->post('period');
    $payment->save();
    $app->redirect('payment/history/'. $payment->personal_info_id);
    //$app->render('payments/history.twig/', compact('payment'));
})->name('postPayment');

$app->get('/payment/history/:studentId', $isAuthenticated, function($studentId) use ($app) {
    //var_dump($payments);
    $payments = \um\models\Payment::join('payment_modes', function($join) {
        $join->on('payment_modes.id', '=', 'payments.payment_mode_id');
    })->join('periods', function($join){
        $join->on('periods.id', '=', 'payments.period_id');
    })->where('personal_info_id', '=', $studentId)->orderBy('created_at', 'desc')->get();
    $app->render('payments/history.twig', compact('payments'));
})->name('getPayment');

$app->get('/payment/transactions', $isAuthenticated, function() use ($app) {
    $payments = \um\models\Payment::join('payment_modes', function($join) {
        $join->on('payment_modes.id', '=', 'payments.payment_mode_id');
    })->join('periods', function($join){
        $join->on('periods.id', '=', 'payments.period_id');
    })->orderBy('created_at', 'desc')->get();
    $app->render('payments/transactions.twig', compact('payments'));
})->name('getPayment');