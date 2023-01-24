<?php
/**
 * Brought to you by ogboo. Feel free to donate to support the development of this module.
 * License: GNU General Public License v3.0

 * https://buymeacoffee.com/ogboo
 * https://ogboo.vip
 * https://github.com/ogboo
*/

// Making sure we run module in WHMCS
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Defining gateway parameters
function zen_MetaData()
{
    return array(
        'DisplayName' => 'ZEN.com',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

// Defining gateway configuration options
function zen_config()
{
    return array(

        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'ZEN.com',
        ),
        'terminaluuid' => array(
            'FriendlyName' => 'Terminal UUID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your account ID here',
        ),
        'secretKey' => array(
            'FriendlyName' => 'Paywall Secret',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Paywall Secret here',
        ),
        'ipnsecretKey' => array(
            'FriendlyName' => 'IPN Secret',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter IPN Secret here',
        ),
    );
}

// Defining payment link
function zen_link($params)
{
    // Gateway Configuration Parameters
    $terminalUUID = $params['terminaluuid'];
    $secretKey = $params['secretKey'];
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // Defining URL of request
    $url = 'https://secure.zen.com/api/payment';

    // Generating unique payment ID
    $num = rand(1, 99999);
    $txid = $invoiceId . '-' . $num . '-' . md5($email . '-' . $num);
    
    // Signature generation
    $signature = hash('sha256', 'amount=' . $amount * 100 . '&currency=' . $currencyCode . '&customIpnUrl=' . $systemUrl . 'modules/gateways/callback/zen.php' . '&customer[email]=' . $email . '&customer[firstName]=' . $firstname . '&customer[lastName]=' . $lastname . '&merchantTransactionId=' . $txid . '&terminalUuid=' . $terminalUUID . '&urlReturn=' . $returnUrl . $secretKey);

    // HTML generation
    $htmlOutput = '<form method="post" name="zen_payment_image" action="' . $url . '">';
    $htmlOutput .= '<input type="hidden" name="terminalUuid" id="terminalUuid" class="field" value="' . $terminalUUID . '" />';
    $htmlOutput .= '<input type="hidden" name="amount" id="amount" class="field" value="' . $amount * 100 . '" />';
    $htmlOutput .= '<input type="hidden" name="currency" id="currency" class="field" value="' . $currencyCode . '" />';
    $htmlOutput .= '<input type="hidden" name="merchantTransactionId" id="merchantTransactionId" class="field" value="' . $txid . '" />';
    $htmlOutput .= '<input type="hidden" name="customer[firstName]" id="customer[firstName]" class="field" value="' . $firstname . '" />';
    $htmlOutput .= '<input type="hidden" name="customer[lastName]" id="customer[lastName]" class="field" value="' . $lastname . '" />';
    $htmlOutput .= '<input type="hidden" name="customer[email]" id="customer[email]" class="field" value="' . $email . '" />';
    $htmlOutput .= '<input type="hidden" name="urlReturn" id="urlReturn" class="field" value="' . $returnUrl . '" />';
    $htmlOutput .= '<input type="hidden" name="customIpnUrl" id="customIpnUrl" class="field" value="' . $systemUrl . 'modules/gateways/callback/zen.php' . '" />';
    $htmlOutput .= '<input type="hidden" name="signature" id="signature" class="field" value="' . $signature . ';sha256' . '" />';
    $htmlOutput .= '<br><a href="#" onclick="document.zen_payment_image.submit();"><img src="' . $systemUrl . 'modules/gateways/zen/assets/paybutton-black.svg' . '"></a>';
    $htmlOutput .= '</form>';
    return $htmlOutput;
}
