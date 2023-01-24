<?php
/**
 * Brought to you by ogboo. Feel free to donate to support the development of this module.
 * License: GNU General Public License v3.0

 * https://buymeacoffee.com/ogboo
 * https://ogboo.vip
 * https://github.com/ogboo
*/

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve body of the callback request.
$rawBody = file_get_contents("php://input");
$input = json_decode($rawBody, true);

// Retrieve data returned in payment gateway callback
$success = $input['status'];
$txiddecode = explode('-', $input['merchantTransactionId']);
$invoiceId = $txiddecode[0];
$transactionId = $input['transactionId'];
$paymentAmount = $input['amount'];
$hash = $input['hash'];

// Settings transactionStatus to success or fail
$transactionStatus = ($success == 'ACCEPTED') ? 'Success' : 'Failure';

// Check if hash is valid
$ipnsecret = $gatewayParams['ipnsecretKey'];
if (strtolower($hash) != hash('sha256', $input['merchantTransactionId'] . $input['currency'] . $input['amount'] . $input['status'] . $ipnsecret)) {
    $transactionStatus = 'Hash Verification Failure';
    $success = false;
}

//Validate callback invoice ID
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

//Check Callback Transaction ID
checkCbTransID($transactionId);

//Log transaction
logTransaction($gatewayParams['name'], $rawBody, $transactionStatus);

if ($success == 'ACCEPTED') {

    //Add invoice payment
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        0,
        $gatewayModuleName
    );

    $data = array("status" => "ok");

    header("Content-Type: application/json");
    echo json_encode($data);
} elseif ($success) {

    // Reply to the IPN not related to invoice payment.

    $data = array("status" => "ok");

    header("Content-Type: application/json");
    echo json_encode($data);
}
