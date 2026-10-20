<?php
// Shelby SSH - modelo de email premium (compartilhado)
function shelby_email_cfg_path() {
    $base = realpath(__DIR__ . '/../../..');
    return $base . '/config/email-template.json';
}
function shelby_email_cfg() {
    $def = array(
        'remetente'  => 'SHELBY SSH',
        'titulo'     => 'Seu acesso está liberado! 🚀',
        'banner'     => '',
        'banner_link'=> '',
        'app_texto'  => '📲 Baixar App',
        'app_link'   => '',
        'wa_texto'   => '💬 Falar no WhatsApp',
        'wa_link'    => '',
        'rodape'     => 'Suporte Técnico',
    );
    $f = shelby_email_cfg_path();
    if (is_file($f)) {
        $j = json_decode(@file_get_contents($f), true);
        if (is_array($j)) $def = array_merge($def, $j);
    }
    return $def;
}
function shelby_url_ok($u) {
    $u = trim((string)$u);
    if ($u === '') return '';
    if (preg_match('~^(https?://|mailto:|tel:)~i', $u)) return $u;
    if (preg_match('~^[\w.-]+\.[a-z]{2,}~i', $u)) return 'https://' . $u;
    return '';
}
function shelby_wa_link($v) {
    $v = trim((string)$v);
    if ($v === '') return '';
    $d = preg_replace('/\D/', '', $v);
    if (!preg_match('~^https?://~i', $v) && strlen($d) >= 10) {
        if (strlen($d) <= 11) $d = '55' . $d;
        return 'https://wa.me/' . $d;
    }
    return shelby_url_ok($v);
}
function shelby_email_html($c, $msg, $dados) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    $banner = shelby_url_ok($c['banner']);
    $blink  = shelby_url_ok($c['banner_link']);
    $app    = shelby_url_ok($c['app_link']);
    $wa     = shelby_wa_link($c['wa_link']);
    $out  = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>';
    $out .= '<body style="margin:0;padding:0;background:#e9edf2;font-family:Arial,Helvetica,sans-serif">';
    $out .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#e9edf2;padding:18px 0"><tr><td align="center">';
    $out .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#0b1430;border-radius:16px;overflow:hidden;border:1px solid #1d2b55">';
    $out .= '<tr><td style="padding:18px 22px;background:#0f1c40;border-bottom:3px solid #f5b81c"><span style="color:#f5b81c;font-size:21px;font-weight:bold;letter-spacing:1px">⚡ ' . $h($c['remetente']) . ' ⚡</span></td></tr>';
    if ($banner) {
        $img = '<img src="' . $h($banner) . '" alt="' . $h($c['remetente']) . '" width="520" style="display:block;width:100%;max-width:520px;height:auto;border:0">';
        $out .= '<tr><td style="line-height:0">' . ($blink ? '<a href="' . $h($blink) . '" target="_blank">' . $img . '</a>' : $img) . '</td></tr>';
    }
    $out .= '<tr><td style="padding:22px 24px 6px;color:#e8eefc;font-size:15px;line-height:1.6">';
    if (trim($c['titulo']) !== '') $out .= '<div style="color:#22d3ee;font-size:19px;font-weight:bold;margin-bottom:8px">' . $h($c['titulo']) . '</div>';
    $out .= nl2br($h($msg)) . '</td></tr>';
    $linhas = '';
    foreach ($dados as $k => $v) {
        if (trim((string)$v) === '') continue;
        $linhas .= '<b style="color:#f5b81c">' . $h($k) . ':</b> <span style="color:#ffffff">' . $h($v) . '</span><br>';
    }
    if ($linhas) $out .= '<tr><td style="padding:12px 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0f1c40;border:1px solid #1f6f86;border-radius:12px"><tr><td style="padding:14px 16px;color:#cfd8f0;font-size:15px;line-height:1.9">' . $linhas . '</td></tr></table></td></tr>';
    if ($app) $out .= '<tr><td align="center" style="padding:14px 24px 4px"><a href="' . $h($app) . '" target="_blank" style="display:block;background:#f5b81c;background-image:linear-gradient(90deg,#f5b81c,#ff9a1f);color:#0b1430;text-decoration:none;font-weight:bold;font-size:16px;padding:15px;border-radius:12px">' . $h($c['app_texto']) . '</a></td></tr>';
    if ($wa) $out .= '<tr><td align="center" style="padding:10px 24px 4px"><a href="' . $h($wa) . '" target="_blank" style="display:block;background:#25d366;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;padding:15px;border-radius:12px">' . $h($c['wa_texto']) . '</a></td></tr>';
    $out .= '<tr><td style="height:20px"></td></tr>';
    $out .= '<tr><td align="center" style="padding:14px;background:#081026;color:#7d8bb3;font-size:12px">© ' . date('Y') . ' ' . $h($c['remetente']) . ' · ' . $h($c['rodape']) . '</td></tr>';
    $out .= '</table></td></tr></table></body></html>';
    return $out;
}
