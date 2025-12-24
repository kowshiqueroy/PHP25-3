<?php
/**
 * Glassy Invoice Email Sender — Complete Revised One‑Page PHP Application
 * Features:
 * - Password-protected access
 * - Glassmorphism UI (Bootstrap + custom CSS)
 * - Quill.js rich text editor (headers, colors, alignment, lists, links)
 * - Multi-recipient (To, CC, BCC), attachments (max 10)
 * - Dynamic invoice builder: add/remove items, qty/price, auto totals
 * - 5 invoice styles (classic, modern, minimal, colorful, corporate)
 * - Invoice metadata: logo URL, From/To details (name, email, phone, address)
 * - Currency, invoice number, date
 * - Option to include invoice in email body
 * - Option to auto-attach invoice as HTML (no third-party libraries)
 * - Mailbox-style preview of composed email
 * - Send via PHP mail() with optional SMTP host/port
 * - After sending, show status alert
 * - Persist all inputs after sending (composer + invoice + editor + rows + totals)
 */

session_start();

/* ========= Configuration ========= */
const APP_TITLE     = "Glassy Invoice Email Sender";
const ACCESS_PASS   = "glassy-2025"; // Change in production
const MAX_ATTACH    = 10;
const DEFAULT_SENDER_NAME  = "Rangpur Traders Ltd.";
const DEFAULT_SENDER_EMAIL = "accounts@rangpurtraders.com";

/* ========= Helpers ========= */
function is_logged_in(): bool {
    return isset($_SESSION['glassy_auth']) && $_SESSION['glassy_auth'] === true;
}
function login($pass): void {
    if (hash_equals(ACCESS_PASS, (string)$pass)) {
        $_SESSION['glassy_auth'] = true;
    }
}
function logout(): void {
    $_SESSION = [];
    session_destroy();
}
function clean($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function posted($name, $fallback = ''): string {
    return isset($_POST[$name]) ? (string)$_POST[$name] : (string)$fallback;
}
function posted_checked($name): string {
    return isset($_POST[$name]) ? 'checked' : '';
}

/* ========= Auth routing ========= */
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    login($_POST['password'] ?? '');
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
if (isset($_GET['logout'])) {
    logout();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* ========= Send email handler ========= */
$send_result = null;
if (is_logged_in() && isset($_POST['action']) && $_POST['action'] === 'send_email') {
    $sender_name   = trim(posted('sender_name', DEFAULT_SENDER_NAME));
    $sender_email  = trim(posted('sender_email', DEFAULT_SENDER_EMAIL));
    $reply_to      = trim(posted('reply_to', $sender_email));
    $subject       = trim(posted('subject', 'Invoice'));
    $to_emails     = trim(posted('to', ''));
    $cc_emails     = trim(posted('cc', ''));
    $bcc_emails    = trim(posted('bcc', ''));
    $smtp_enable   = (posted('smtp_enable') === '1');
    $smtp_host     = trim(posted('smtp_host', ''));
    $smtp_port     = trim(posted('smtp_port', ''));

    $include_invoice = isset($_POST['include_invoice']);
    $attach_invoice  = isset($_POST['attach_invoice']);

    // Editor body and composed HTML
    $editor_body_html  = (string)posted('editor_body', '');
    $final_html        = (string)posted('html_body', '');
    $invoice_only_html = (string)posted('invoice_only_html', '');

    // Basic checks
    if ($to_emails === '' || !filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $send_result = ['ok' => false, 'msg' => 'Invalid To recipients or sender email.'];
    } else {
        // Optional SMTP routing for Windows PHP
        if ($smtp_enable && $smtp_host !== '') {
            ini_set('SMTP', $smtp_host);
            if ($smtp_port !== '') ini_set('smtp_port', $smtp_port);
        }

        // Recipients
        $to_list  = array_filter(array_map('trim', preg_split('/[,;\s]+/', $to_emails)));
        $cc_list  = array_filter(array_map('trim', preg_split('/[,;\s]+/', $cc_emails)));
        $bcc_list = array_filter(array_map('trim', preg_split('/[,;\s]+/', $bcc_emails)));

        // MIME boundaries
        $boundary     = '==BOUNDARY_'.md5(uniqid((string)mt_rand(), true));
        $alt_boundary = '==ALT_'.md5(uniqid((string)mt_rand(), true));
        $headers = [];

        $headers[] = 'From: '.sprintf('"%s" <%s>', mb_encode_mimeheader($sender_name, 'UTF-8'), $sender_email);
        $headers[] = 'Reply-To: '.$reply_to;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
        if (!empty($cc_list))  $headers[] = 'Cc: '.implode(', ', $cc_list);
        if (!empty($bcc_list)) $headers[] = 'Bcc: '.implode(', ', $bcc_list);

        // Plain text fallback stripped from editor body and invoice (if included)
        $plain_body = strip_tags(preg_replace('/<br\\s*\\/?>/i', "\n", $editor_body_html.($include_invoice ? ("\n".$invoice_only_html) : '')));
        if ($plain_body === '') { $plain_body = 'This email contains formatted content. Please view in an HTML-enabled client.'; }

        // Build message body
        $message = '';
        $message .= "--{$boundary}\r\n";
        $message .= 'Content-Type: multipart/alternative; boundary="'.$alt_boundary."\"\r\n\r\n";

        // Plain part
        $message .= "--{$alt_boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $plain_body."\r\n\r\n";

        // HTML part (final composed HTML)
        $message .= "--{$alt_boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $final_html."\r\n\r\n";
        $message .= "--{$alt_boundary}--\r\n";

        // Auto-attach invoice HTML, if requested and included
        if ($include_invoice && $attach_invoice && $invoice_only_html !== '') {
            $tmpFile = tempnam(sys_get_temp_dir(), "inv").".html";
            @file_put_contents($tmpFile, $invoice_only_html);
            if (is_file($tmpFile)) {
                $_FILES['attachments']['name'][]     = "invoice.html";
                $_FILES['attachments']['type'][]     = "text/html";
                $_FILES['attachments']['tmp_name'][] = $tmpFile;
                $_FILES['attachments']['error'][]    = UPLOAD_ERR_OK;
                $_FILES['attachments']['size'][]     = filesize($tmpFile);
            }
        }

        // Attachments
        $files_attached = 0;
        if (!empty($_FILES['attachments']['name'][0])) {
            $count = min(count($_FILES['attachments']['name']), MAX_ATTACH);
            for ($i = 0; $i < $count; $i++) {
                $name = $_FILES['attachments']['name'][$i];
                $type = $_FILES['attachments']['type'][$i] ?: 'application/octet-stream';
                $tmp  = $_FILES['attachments']['tmp_name'][$i];
                $err  = $_FILES['attachments']['error'][$i];
                if ($err === UPLOAD_ERR_OK && is_uploaded_file($tmp)) {
                    $data = file_get_contents($tmp);
                    if ($data !== false) {
                        $files_attached++;
                        $message .= "--{$boundary}\r\n";
                        $message .= "Content-Type: {$type}; name=\"".addslashes($name)."\"\r\n";
                        $message .= "Content-Transfer-Encoding: base64\r\n";
                        $message .= "Content-Disposition: attachment; filename=\"".addslashes($name)."\"\r\n\r\n";
                        $message .= chunk_split(base64_encode($data))."\r\n";
                    }
                } elseif ($err === UPLOAD_ERR_OK && is_file($tmp)) {
                    // For our temp invoice.html (created as file, not an uploaded temp)
                    $data = file_get_contents($tmp);
                    if ($data !== false) {
                        $files_attached++;
                        $message .= "--{$boundary}\r\n";
                        $message .= "Content-Type: text/html; name=\"invoice.html\"\r\n";
                        $message .= "Content-Transfer-Encoding: base64\r\n";
                        $message .= "Content-Disposition: attachment; filename=\"invoice.html\"\r\n\r\n";
                        $message .= chunk_split(base64_encode($data))."\r\n";
                    }
                }
            }
        }

        // End boundary
        $message .= "--{$boundary}--\r\n";

        // Send
        $to_header = implode(', ', $to_list);
        $ok = @mail($to_header, mb_encode_mimeheader($subject, 'UTF-8'), $message, implode("\r\n", $headers));
        $send_result = [
            'ok'  => $ok ? true : false,
            'msg' => $ok
                ? ("Email sent to ".count($to_list)." recipient(s)".($files_attached ? " with {$files_attached} attachment(s)." : "."))
                : "Failed to send email. Check server mail configuration or SMTP settings."
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= clean(APP_TITLE) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Quill -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Glassmorphism base */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(32, 40, 62, 1), rgba(58, 96, 115, 1)) fixed,
                        url('https://images.unsplash.com/photo-1520975612263-3ed450b21c71?q=80&w=1200&auto=format&fit=crop') center/cover fixed;
            color: #f5f7fb;
        }
        .glassy {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 20px 40px rgba(0,0,0,0.35);
            backdrop-filter: blur(14px) saturate(140%);
            -webkit-backdrop-filter: blur(14px) saturate(140%);
        }
        .glassy-light {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.28);
            backdrop-filter: blur(10px);
        }
        .text-muted-light { color: rgba(245,247,251,0.85); }
        .form-control, .form-select {
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(255,255,255,0.6);
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.35);
        }
        .btn-glass {
            color: #1c2540;
            background: rgba(255,255,255,0.85);
            border: 1px solid rgba(255,255,255,0.7);
        }
        .btn-glass:hover { background: #fff; }
        .brand-title { font-weight: 700; letter-spacing: 0.2px; }
        /* Mailbox preview */
        .mailbox-card { background: #0e1726; border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; overflow: hidden; }
        .mailbox-header { background: #0b1321; padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .mailbox-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .mailbox-avatar { background: #243b55; color: #fff; border-radius: 50%; width: 38px; height: 38px; display:flex; align-items:center; justify-content:center; font-weight: 600; }
        .mailbox-subject { font-weight: 600; }
        .mailbox-body { padding: 16px; background: #101a2e; color: #e8ecf5; }
        .mailbox-body .invoice { background: #0e1726; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px; margin-top: 12px; }
        /* Invoice style themes */
        .invoice.classic { font-family: "Georgia", serif; }
        .invoice.modern  { font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .invoice.minimal { font-family: "Inter", system-ui, -apple-system; border-left: 4px solid #e2e8f0; }
        .invoice.colorful { font-family: "Inter", system-ui, -apple-system; }
        .invoice.colorful h2 { color: #ff7b54; }
        .invoice.corporate { font-family: "Segoe UI", Tahoma, Verdana, sans-serif; }
        .table-invoice th, .table-invoice td { padding: 8px 10px; border-bottom: 1px dashed rgba(255,255,255,0.08); }
        .section-title { font-size: 1rem; font-weight: 600; opacity: 0.95; }
        #editor { height: 220px; }
        .footer { color: rgba(255,255,255,0.85); font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container py-4 py-md-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="brand-title h4 mb-0"><?= clean(APP_TITLE) ?></div>
        <?php if (is_logged_in()): ?>
            <a href="?logout=1" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <?php endif; ?>
    </div>

    <?php if (!is_logged_in()): ?>
        <!-- Login Card -->
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 glassy p-4 p-md-5">
                <div class="mb-3">
                    <div class="h5 mb-1">Access</div>
                    <div class="text-muted-light">Enter the password to continue.</div>
                </div>
                <form method="post" class="row g-3">
                    <input type="hidden" name="action" value="login">
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter access password" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-glass btn-lg"><i class="bi bi-shield-lock"></i> Unlock</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <?php if ($send_result !== null): ?>
            <div class="alert <?= $send_result['ok'] ? 'alert-success' : 'alert-danger' ?> glassy-light">
                <?= clean($send_result['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Composer + Invoice + Preview -->
        <div class="row g-4">
            <!-- Left: Composer -->
            <div class="col-12 col-lg-7">
                <div class="glassy p-3 p-md-4">
                    <div class="section-title mb-3">Compose email</div>
                    <form id="composerForm" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="send_email">
                        <!-- Hidden fields for persistence -->
                        <input type="hidden" name="html_body" id="html_body" value="">
                        <input type="hidden" name="editor_body" id="editor_body" value="<?= isset($_POST['editor_body']) ? clean($_POST['editor_body']) : '' ?>">
                        <input type="hidden" name="invoice_only_html" id="invoice_only_html" value="<?= isset($_POST['invoice_only_html']) ? clean($_POST['invoice_only_html']) : '' ?>">
                        <input type="hidden" name="invoiceRows" id="invoiceRows" value="<?= clean(posted('invoiceRows','[]')) ?>">
                        <input type="hidden" name="subtotal" id="subtotalHidden" value="<?= clean(posted('subtotal','0.00')) ?>">
                        <input type="hidden" name="tax" id="taxHidden" value="<?= clean(posted('tax','0.00')) ?>">
                        <input type="hidden" name="total" id="totalHidden" value="<?= clean(posted('total','0.00')) ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Sender name</label>
                                <input type="text" class="form-control" name="sender_name" id="sender_name" value="<?= clean(posted('sender_name', DEFAULT_SENDER_NAME)) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Sender email</label>
                                <input type="email" class="form-control" name="sender_email" id="sender_email" value="<?= clean(posted('sender_email', DEFAULT_SENDER_EMAIL)) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Reply-To</label>
                                <input type="email" class="form-control" name="reply_to" id="reply_to" value="<?= clean(posted('reply_to', DEFAULT_SENDER_EMAIL)) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">To (comma separated)</label>
                                <input type="text" class="form-control" name="to" id="to" placeholder="recipient1@example.com, recipient2@example.com" value="<?= clean(posted('to')) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">CC</label>
                                <input type="text" class="form-control" name="cc" id="cc" placeholder="optional, comma separated" value="<?= clean(posted('cc')) ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">BCC</label>
                                <input type="text" class="form-control" name="bcc" id="bcc" placeholder="optional, comma separated" value="<?= clean(posted('bcc')) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" id="subject" placeholder="Invoice for ..." value="<?= clean(posted('subject')) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Email body (rich text)</label>
                                <div id="toolbar" class="glassy-light p-2 mb-2">
                                    <span class="ql-formats">
                                        <select class="ql-header">
                                            <option selected></option>
                                            <option value="1"></option>
                                            <option value="2"></option>
                                        </select>
                                        <select class="ql-font"></select>
                                        <select class="ql-size"></select>
                                    </span>
                                    <span class="ql-formats">
                                        <button class="ql-bold"></button>
                                        <button class="ql-italic"></button>
                                        <button class="ql-underline"></button>
                                        <button class="ql-color"></button>
                                        <button class="ql-background"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button class="ql-list" value="ordered"></button>
                                        <button class="ql-list" value="bullet"></button>
                                        <button class="ql-align" value=""></button>
                                        <button class="ql-align" value="center"></button>
                                        <button class="ql-align" value="right"></button>
                                        <button class="ql-blockquote"></button>
                                    </span>
                                    <span class="ql-formats">
                                        <button class="ql-link"></button>
                                    </span>
                                </div>
                                <div id="editor" class="glassy-light"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Attachments</label>
                                <input type="file" class="form-control" name="attachments[]" id="attachments" multiple>
                                <div class="form-text">Max <?= MAX_ATTACH ?> attachments.</div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="smtp_enable" name="smtp_enable" value="1" <?= posted('smtp_enable') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smtp_enable">Use SMTP host (Windows PHP mail routing)</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label">SMTP host</label>
                                <input type="text" class="form-control" name="smtp_host" id="smtp_host" value="<?= clean(posted('smtp_host')) ?>" placeholder="mail.yourdomain.com">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">SMTP port</label>
                                <input type="text" class="form-control" name="smtp_port" id="smtp_port" value="<?= clean(posted('smtp_port')) ?>" placeholder="25">
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="button" id="btnPreview" class="btn btn-glass"><i class="bi bi-eye"></i> Preview</button>
                                <button type="button" id="btnAutoFill" class="btn btn-outline-light"><i class="bi bi-magic"></i> Auto-Fill Demo Invoice</button>
                                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-send"></i> Send email</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Invoice Builder + Preview -->
            <div class="col-12 col-lg-5">
                <div class="glassy p-3 p-md-4 mb-4">
                    <div class="section-title mb-3">Invoice builder</div>

                    <!-- Invoice options -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Invoice style</label>
                            <select id="invoiceStyle" name="invoiceStyle" class="form-select" form="composerForm">
                                <option value="classic" <?= posted('invoiceStyle')==='classic'?'selected':'' ?>>Classic</option>
                                <option value="modern" <?= posted('invoiceStyle','modern')==='modern'?'selected':'' ?>>Modern</option>
                                <option value="minimal" <?= posted('invoiceStyle')==='minimal'?'selected':'' ?>>Minimal</option>
                                <option value="colorful" <?= posted('invoiceStyle')==='colorful'?'selected':'' ?>>Colorful</option>
                                <option value="corporate" <?= posted('invoiceStyle')==='corporate'?'selected':'' ?>>Corporate</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Currency</label>
                            <input type="text" id="currency" name="currency" class="form-control" value="<?= clean(posted('currency','BDT')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Invoice number</label>
                            <input type="text" id="invoiceNumber" name="invoiceNumber" class="form-control" value="<?= clean(posted('invoiceNumber','INV-2025-001')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Invoice date</label>
                            <input type="date" id="invoiceDate" name="invoiceDate" class="form-control" value="<?= clean(posted('invoiceDate')) ?>" form="composerForm">
                        </div>
                    </div>

                    <!-- Logo + metadata -->
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Logo URL</label>
                            <input type="text" id="invoiceLogo" name="invoiceLogo" class="form-control" placeholder="https://example.com/logo.png" value="<?= clean(posted('invoiceLogo')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">From company</label>
                            <input type="text" id="fromCompany" name="fromCompany" class="form-control" value="<?= clean(posted('fromCompany','Rangpur Traders Ltd.')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">From email</label>
                            <input type="email" id="fromEmail" name="fromEmail" class="form-control" value="<?= clean(posted('fromEmail', DEFAULT_SENDER_EMAIL)) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">From phone</label>
                            <input type="text" id="fromPhone" name="fromPhone" class="form-control" value="<?= clean(posted('fromPhone')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">From address</label>
                            <input type="text" id="fromAddress" name="fromAddress" class="form-control" value="<?= clean(posted('fromAddress', 'Nilphamari, Rangpur, Bangladesh')) ?>" form="composerForm">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Bill to (name)</label>
                            <input type="text" id="toName" name="toName" class="form-control" value="<?= clean(posted('toName')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Bill to email</label>
                            <input type="email" id="toEmail" name="toEmail" class="form-control" value="<?= clean(posted('toEmail')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Bill to phone</label>
                            <input type="text" id="toPhone" name="toPhone" class="form-control" value="<?= clean(posted('toPhone')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Bill to address</label>
                            <input type="text" id="toAddress" name="toAddress" class="form-control" value="<?= clean(posted('toAddress')) ?>" form="composerForm">
                        </div>
                        <div class="col-12 d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeInvoice" name="include_invoice" form="composerForm" <?= posted_checked('include_invoice') ?>>
                                <label class="form-check-label" for="includeInvoice">Include invoice in email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="attachInvoice" name="attach_invoice" form="composerForm" <?= posted_checked('attach_invoice') ?>>
                                <label class="form-check-label" for="attachInvoice">Auto-attach invoice HTML</label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive glassy-light p-2">
                        <table class="table table-sm align-middle table-invoice mb-0" id="invoiceTable">
                            <thead>
                                <tr>
                                    <th style="width:40%">Item</th>
                                    <th style="width:15%">Qty</th>
                                    <th style="width:20%">Price</th>
                                    <th style="width:20%">Line total</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceBody">
                                <?php
                                // Restore invoice rows from posted JSON, else seed demo
                                $rows_json = posted('invoiceRows','[]');
                                $rows = json_decode($rows_json, true);
                                if (is_array($rows) && count($rows) > 0) {
                                    foreach ($rows as $r) {
                                        $item  = clean($r['item'] ?? '');
                                        $qty   = (float)($r['qty'] ?? 0);
                                        $price = (float)($r['price'] ?? 0);
                                        $line  = $qty * $price;
                                        echo '<tr>';
                                        echo '<td><input type="text" class="form-control form-control-sm item" value="'.$item.'" placeholder="Description"></td>';
                                        echo '<td><input type="number" class="form-control form-control-sm qty" min="0" step="1" value="'.$qty.'"></td>';
                                        echo '<td><input type="number" class="form-control form-control-sm price" min="0" step="0.01" value="'.$price.'"></td>';
                                        echo '<td class="line-total">'.number_format($line,2).'</td>';
                                        echo '<td><button class="btn btn-sm btn-outline-danger remove"><i class="bi bi-x-lg"></i></button></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    // Seed two demo rows (JS will calculate totals)
                                    echo '<tr>
                                            <td><input type="text" class="form-control form-control-sm item" value="Consulting Services" placeholder="Description"></td>
                                            <td><input type="number" class="form-control form-control-sm qty" min="0" step="1" value="10"></td>
                                            <td><input type="number" class="form-control form-control-sm price" min="0" step="0.01" value="1500"></td>
                                            <td class="line-total">15000.00</td>
                                            <td><button class="btn btn-sm btn-outline-danger remove"><i class="bi bi-x-lg"></i></button></td>
                                          </tr>';
                                    echo '<tr>
                                            <td><input type="text" class="form-control form-control-sm item" value="Travel & Accommodation" placeholder="Description"></td>
                                            <td><input type="number" class="form-control form-control-sm qty" min="0" step="1" value="1"></td>
                                            <td><input type="number" class="form-control form-control-sm price" min="0" step="0.01" value="5000"></td>
                                            <td class="line-total">5000.00</td>
                                            <td><button class="btn btn-sm btn-outline-danger remove"><i class="bi bi-x-lg"></i></button></td>
                                          </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <button class="btn btn-sm btn-glass" id="addRow"><i class="bi bi-plus-circle"></i> Add item</button>
                        <div class="text-end">
                            <div><strong>Subtotal:</strong> <span id="subtotal"><?= clean(posted('subtotal','0.00')) ?></span></div>
                            <div><strong>Tax (10%):</strong> <span id="tax"><?= clean(posted('tax','0.00')) ?></span></div>
                            <div class="fs-5"><strong>Total:</strong> <span id="total"><?= clean(posted('total','0.00')) ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="glassy p-3 p-md-4">
                    <div class="section-title mb-3">Mailbox-style preview</div>
                    <div class="mailbox-card">
                        <div class="mailbox-header d-flex justify-content-between">
                            <div><i class="bi bi-inbox"></i> Inbox</div>
                            <div class="small text-muted-light">Preview only</div>
                        </div>
                        <div class="mailbox-item">
                            <div class="mailbox-avatar" id="previewAvatar">RT</div>
                            <div class="flex-grow-1">
                                <div class="mailbox-subject" id="previewSubject">Subject will appear here</div>
                                <div class="text-muted-light small" id="previewSender">From: Sender Name &lt;email@domain&gt;</div>
                            </div>
                            <div class="badge bg-info-subtle text-dark">New</div>
                        </div>
                        <div class="mailbox-body">
                            <div id="previewBody">Formatted email body preview will render here.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center footer">
            Built for rapid demos in Nilphamari, Rangpur — mobile-friendly, glassy, and production-ready scaffolding.
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function(){
    // Initialize Quill
    const quill = new Quill('#editor', {
        modules: { toolbar: '#toolbar' },
        theme: 'snow'
    });

    // Restore editor content if posted
    const postedEditor = document.getElementById('editor_body').value;
    if (postedEditor) {
        quill.setContents([]);
        quill.clipboard.dangerouslyPasteHTML(postedEditor);
    }

    // Default date if empty
    const dateInput = document.getElementById('invoiceDate');
    if (dateInput && !dateInput.value) {
        const today = new Date();
        dateInput.value = today.toISOString().substring(0,10);
    }

    // Invoice rows interaction
    const invoiceBody = document.getElementById('invoiceBody');
    const addRowBtn   = document.getElementById('addRow');
    const subtotalEl  = document.getElementById('subtotal');
    const taxEl       = document.getElementById('tax');
    const totalEl     = document.getElementById('total');

    function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
    function parseNumber(v){ const n = parseFloat(v); return isNaN(n) ? 0 : n; }

    function rowTemplate(item = "", qty = 1, price = 0.00, line = 0.00) {
        return `
            <tr>
                <td><input type="text" class="form-control form-control-sm item" placeholder="Description" value="${escapeHtml(item)}"></td>
                <td><input type="number" class="form-control form-control-sm qty" min="0" step="1" value="${qty}"></td>
                <td><input type="number" class="form-control form-control-sm price" min="0" step="0.01" value="${price}"></td>
                <td class="line-total">${Number(line).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-outline-danger remove"><i class="bi bi-x-lg"></i></button></td>
            </tr>
        `;
    }

    function addRow(item = "", qty = 1, price = 0.00) {
        const tr = document.createElement('tr');
        tr.innerHTML = rowTemplate(item, qty, price, qty * price);
        invoiceBody.appendChild(tr);
        updateTotals();
    }

    function updateTotals(){
        let subtotal = 0;
        [...invoiceBody.querySelectorAll('tr')].forEach(tr => {
            const qty = parseNumber(tr.querySelector('.qty').value);
            const price = parseNumber(tr.querySelector('.price').value);
            const line = qty * price;
            tr.querySelector('.line-total').textContent = line.toFixed(2);
            subtotal += line;
        });
        const tax = subtotal * 0.10;
        const total = subtotal + tax;
        subtotalEl.textContent = subtotal.toFixed(2);
        taxEl.textContent = tax.toFixed(2);
        totalEl.textContent = total.toFixed(2);
    }

    addRowBtn.addEventListener('click', () => addRow());
    invoiceBody.addEventListener('input', (e) => {
        if (e.target.classList.contains('qty') || e.target.classList.contains('price') || e.target.classList.contains('item')) {
            updateTotals();
        }
    });
    invoiceBody.addEventListener('click', (e) => {
        if (e.target.closest('.remove')) {
            e.preventDefault();
            e.target.closest('tr').remove();
            updateTotals();
        }
    });

    // If posted rows existed, totals may not be accurate; recalc them
    updateTotals();

    // Build invoice HTML based on style + metadata
    function buildInvoiceHTML() {
        const style = document.getElementById('invoiceStyle').value;
        const currency = document.getElementById('currency').value || 'BDT';
        const invNo = document.getElementById('invoiceNumber').value || 'INV-0000';
        const invDate = document.getElementById('invoiceDate').value || '';

        const logo = document.getElementById('invoiceLogo').value || '';
        const fromCompany = document.getElementById('fromCompany').value || '';
        const fromEmail   = document.getElementById('fromEmail').value || '';
        const fromPhone   = document.getElementById('fromPhone').value || '';
        const fromAddress = document.getElementById('fromAddress').value || '';

        const toName    = document.getElementById('toName').value || '';
        const toEmail   = document.getElementById('toEmail').value || '';
        const toPhone   = document.getElementById('toPhone').value || '';
        const toAddress = document.getElementById('toAddress').value || '';

        const rows = [...invoiceBody.querySelectorAll('tr')].map(tr => {
            return {
                item: tr.querySelector('.item').value,
                qty: parseNumber(tr.querySelector('.qty').value),
                price: parseNumber(tr.querySelector('.price').value),
                line: parseNumber(tr.querySelector('.line-total').textContent)
            };
        });

        const subtotal = parseNumber(subtotalEl.textContent);
        const tax = parseNumber(taxEl.textContent);
        const total = parseNumber(totalEl.textContent);

        const headerColor = {
            classic: '#d1d5db',
            modern: '#93c5fd',
            minimal: '#cbd5e1',
            colorful: '#ff7b54',
            corporate: '#60a5fa'
        }[style];

        const tableHeadBg = {
            classic: 'rgba(255,255,255,0.06)',
            modern: 'rgba(147,197,253,0.12)',
            minimal: 'rgba(203,213,225,0.12)',
            colorful: 'rgba(255,123,84,0.12)',
            corporate: 'rgba(96,165,250,0.12)'
        }[style];

        const title = style === 'classic' ? 'Invoice' :
                      style === 'modern' ? 'Invoice • Modern' :
                      style === 'minimal' ? 'Invoice' :
                      style === 'colorful' ? 'Invoice ✦' : 'Invoice • Corporate';

        const linesHTML = rows.map(r => `
            <tr>
                <td>${escapeHtml(r.item)}</td>
                <td style="text-align:right">${r.qty}</td>
                <td style="text-align:right">${currency} ${r.price.toFixed(2)}</td>
                <td style="text-align:right">${currency} ${r.line.toFixed(2)}</td>
            </tr>
        `).join('');

        const logoHTML = logo ? `<img src="${escapeHtml(logo)}" alt="Logo" style="max-height:80px;max-width:180px;object-fit:contain;">` : '';

        return `
            <div class="invoice ${style}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                    <div style="display:flex;gap:12px;align-items:center;">
                        ${logoHTML}
                        <div>
                            <h2 style="margin:0 0 6px 0;color:${headerColor};">${title}</h2>
                            <div style="opacity:0.85">No: ${escapeHtml(invNo)}</div>
                            <div style="opacity:0.85">Date: ${escapeHtml(invDate)}</div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600;">${escapeHtml(fromCompany)}</div>
                        <div style="opacity:0.85">${escapeHtml(fromEmail)}</div>
                        <div style="opacity:0.85">${escapeHtml(fromPhone)}</div>
                        <div style="opacity:0.85">${escapeHtml(fromAddress)}</div>
                    </div>
                </div>
                <div style="margin-top:8px;display:flex;justify-content:space-between;gap:12px;">
                    <div><strong>Bill To:</strong><br>
                        ${escapeHtml(toName)}<br>
                        <span style="opacity:0.85">${escapeHtml(toEmail)}</span><br>
                        <span style="opacity:0.85">${escapeHtml(toPhone)}</span><br>
                        <span style="opacity:0.85">${escapeHtml(toAddress)}</span>
                    </div>
                </div>
                <div style="margin-top:10px;overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead style="background:${tableHeadBg}">
                            <tr>
                                <th style="text-align:left;padding:8px;">Item</th>
                                <th style="text-align:right;padding:8px;">Qty</th>
                                <th style="text-align:right;padding:8px;">Price</th>
                                <th style="text-align:right;padding:8px;">Line</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${linesHTML}
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                    <table style="min-width:280px;">
                        <tr><td style="padding:6px 8px;">Subtotal</td><td style="padding:6px 8px;text-align:right;">${currency} ${subtotal.toFixed(2)}</td></tr>
                        <tr><td style="padding:6px 8px;">Tax (10%)</td><td style="padding:6px 8px;text-align:right;">${currency} ${tax.toFixed(2)}</td></tr>
                        <tr><td style="padding:6px 8px;font-weight:700;">Total</td><td style="padding:6px 8px;text-align:right;font-weight:700;">${currency} ${total.toFixed(2)}</td></tr>
                    </table>
                </div>
                <div style="margin-top:12px;font-size:0.95em;opacity:0.9;">Thank you for your business.</div>
            </div>
        `;
    }

    // Preview rendering
    const previewAvatar  = document.getElementById('previewAvatar');
    const previewSubject = document.getElementById('previewSubject');
    const previewSender  = document.getElementById('previewSender');
    const previewBody    = document.getElementById('previewBody');
    const btnPreview     = document.getElementById('btnPreview');

    function initials(name) {
        return (name || 'NA').split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join('') || 'NA';
    }
    function renderPreview() {
        const senderName = document.getElementById('sender_name').value;
        const senderEmail = document.getElementById('sender_email').value;
        const subject = document.getElementById('subject').value;
        const deltaHtml = quill.root.innerHTML;
        const includeInv = document.getElementById('includeInvoice').checked;
        const invoiceHtml = buildInvoiceHTML();

        previewAvatar.textContent = initials(senderName);
        previewSubject.textContent = subject || 'No subject';
        previewSender.innerHTML = `From: ${escapeHtml(senderName)} &lt;${escapeHtml(senderEmail)}&gt;`;

        previewBody.innerHTML = `
            <div class="email-content">${deltaHtml}</div>
            ${includeInv ? invoiceHtml : ''}
        `;
    }
    btnPreview.addEventListener('click', renderPreview);

    // Auto-Fill Demo
    const btnAutoFill = document.getElementById('btnAutoFill');
    btnAutoFill.addEventListener('click', () => {
        document.getElementById('subject').value = 'Invoice INV-2025-001 • Consulting & Travel';
        const style = document.getElementById('invoiceStyle').value;
        const introTitle = style === 'colorful' ? 'Your Project Summary' : 'Invoice summary';
        quill.setContents([]);
        quill.clipboard.dangerouslyPasteHTML(`
            <h2 style="margin:0 0 6px 0;">${introTitle}</h2>
            <p>Dear client,</p>
            <p>Please find attached the invoice for the services rendered. The breakdown and total are included below.</p>
            <ul>
                <li>Engagement: Systems Architecture & API design</li>
                <li>Period: Dec 01 – Dec 24, 2025</li>
                <li>Terms: Net 15</li>
            </ul>
            <p style="margin-top:8px;">If you have any questions, reply to this email. Thank you for your business.</p>
            <p style="margin-top:10px;"><strong>${escapeHtml(document.getElementById('sender_name').value)}</strong><br>
            <span style="opacity:0.85;">${escapeHtml(document.getElementById('sender_email').value)}</span></p>
        `);
        renderPreview();
    });

    // Before submit: serialize invoice and compose final HTML
    const composerForm = document.getElementById('composerForm');
    composerForm.addEventListener('submit', (e) => {
        // Rows JSON
        const rows = [...invoiceBody.querySelectorAll('tr')].map(tr => ({
            item: tr.querySelector('.item').value,
            qty: tr.querySelector('.qty').value,
            price: tr.querySelector('.price').value
        }));
        document.getElementById('invoiceRows').value = JSON.stringify(rows);

        // Totals persistence
        document.getElementById('subtotalHidden').value = subtotalEl.textContent;
        document.getElementById('taxHidden').value = taxEl.textContent;
        document.getElementById('totalHidden').value = totalEl.textContent;

        // Include invoice toggle
        const includeInv = document.getElementById('includeInvoice').checked;

        // Editor-only body
        const deltaHtml = quill.root.innerHTML;
        document.getElementById('editor_body').value = deltaHtml;

        // Invoice-only HTML for potential attachment
        const invoiceHtml = buildInvoiceHTML();
        document.getElementById('invoice_only_html').value = includeInv ? invoiceHtml : '';

        // Final composed HTML
        const finalHtml = `
            <div style="font-family: Inter, Arial, sans-serif; color:#111;">
                ${deltaHtml}
                ${includeInv ? invoiceHtml : ''}
            </div>
        `;
        document.getElementById('html_body').value = finalHtml;
    });

    // Invoice toggle refreshes preview
    document.getElementById('includeInvoice').addEventListener('change', renderPreview);
})();
</script>
</body>
</html>