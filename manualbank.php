<?php

use WHMCS\Database\Capsule;

function manualbank_config() {
    return [
        'FriendlyName' => ['Type' => 'System', 'Value' => 'Manual Bank Transfer'],
        'bankdetails' => [
            'FriendlyName' => 'Bank Details',
            'Type' => 'textarea',
            'Description' => 'Enter bank info (Bank name, Account, Branch, SWIFT, Routing)',
            'Default' => "Bank Name: XYZ Bank\nAccount Number: 2103738506001\nBranch: Dhaka\nSWIFT Code: CBLTBDDH\nRouting Number: 123456789"
        ],
        'discord_webhook' => [
            'FriendlyName' => 'Discord Webhook URL',
            'Type' => 'text',
            'Size' => '100',
            'Description' => 'Paste your Discord webhook URL'
        ],
    ];
}

function manualbank_link($params) {
    $invoiceId = $params['invoiceid'];
    $bankDetails = nl2br($params['bankdetails']);
    $discordWebhook = $params['discord_webhook'];

    // Ensure table exists
    try {
        if (!Capsule::schema()->hasTable('mod_manualbank_trx')) {
            Capsule::schema()->create('mod_manualbank_trx', function ($table) {
                $table->increments('id');
                $table->integer('invoice_id');
                $table->string('trx_id');
                $table->timestamp('submitted_at')->useCurrent();
            });
        }
    } catch (\Exception $e) {
        logModuleCall('manualbank', 'table_create_error', '', $e->getMessage());
    }

    // Handle TRX ID submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_trx'])) {
        $trxid = trim($_POST['trxid']);
        $submittedAt = date('Y-m-d H:i:s');

        try {
            Capsule::table('mod_manualbank_trx')->insert([
                'invoice_id' => $invoiceId,
                'trx_id' => $trxid,
                'submitted_at' => $submittedAt,
            ]);

            // Discord webhook
            if (!empty($discordWebhook)) {
                $msg = json_encode(["content" => "**New Manual Payment Submitted**\nInvoice: #$invoiceId\nTRX ID: `$trxid`"]);
                $ch = curl_init($discordWebhook);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }

            $_SESSION['manualbank_trx_submit_time_' . $invoiceId] = time();
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;

        } catch (\Exception $e) {
            logModuleCall('manualbank', 'trx_insert_error', '', $e->getMessage());
        }
    }

    // Countdown logic
    $countdownHTML = '';
    $submittedTime = $_SESSION['manualbank_trx_submit_time_' . $invoiceId] ?? null;

    if ($submittedTime) {
        $remaining = max(0, ($submittedTime + 1800) - time());
        $countdownHTML = <<<HTML
        <div class="alert alert-info mt-3">
            <strong>We received your TRX ID. Please wait for verification.</strong><br>
            Time left: <span id="countdown"></span>
        </div>
        <script>
        var time = {$remaining};
        function updateCountdown() {
            var min = Math.floor(time / 60);
            var sec = time % 60;
            document.getElementById('countdown').innerText = min + "m " + (sec < 10 ? "0" : "") + sec + "s";
            if (time-- > 0) setTimeout(updateCountdown, 1000);
        }
        updateCountdown();
        </script>
        HTML;
    }

    return <<<HTML
        <div>
            <h3>Bank Payment Details</h3>
            <div style="border:1px solid #ccc;padding:10px;margin-bottom:15px;">$bankDetails</div>

            <form method="post">
                <label>Enter your Bank TRX ID:</label><br>
                <input type="text" name="trxid" required class="form-control" placeholder="CT/FT-CBLTA-XXXX..." style="margin-bottom:10px;"><br>
                <input type="submit" name="submit_trx" value="Submit Transaction" class="btn btn-primary">
            </form>

            $countdownHTML
        </div>
    HTML;
}
