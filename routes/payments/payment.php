<?php
/**
 * Created by PhpStorm.
 * User: kharl
 * Date: 22/09/2016
 * Time: 4:28 PM
 */
$app->get('/payment', function() use ($app) {
    $paymentModes = \um\models\PaymentMode::all();
    $periods = \um\models\Period::all();
    $app->render('payments/index.twig', compact('paymentModes', 'periods'));
})->name('getPayment');

$app->post('/payment', function() use ($app) {
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

$app->get('/payment/history/:studentId', function($studentId) use ($app) {
    $payments = \um\models\Payment::join('payment_modes', function($join) {
        $join->on('payment_modes.id', '=', 'payments.payment_mode_id');
    })->where('personal_info_id', '=', $studentId)->orderBy('created_at', 'desc')->get();
    //var_dump($payments);
    $app->render('payments/history.twig', compact('payments'));
})->name('getPayment');
