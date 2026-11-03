<?php

final class SecurityHeaders
{
    private static $nonce;

    public static function nonce(): string
    {
        if (self::$nonce !== null) { return self::$nonce; }

        // AJAX fragments belong to the existing document and need its nonce.
        $candidate = $_SERVER['HTTP_X_CSP_NONCE'] ?? '';
        $known = $_SESSION['_csp_nonces'] ?? array();
        if (is_string($candidate) && in_array($candidate, $known, true)) {
            self::$nonce = $candidate;
        } else {
            self::$nonce = base64_encode(random_bytes(24));
            $known[] = self::$nonce;
            $_SESSION['_csp_nonces'] = array_slice($known, -16);
        }
        return self::$nonce;
    }

    public static function send(): void
    {
        $nonce = self::nonce();
        // Vue's existing template compiler needs eval. Arbitrary inline scripts
        // still require a server nonce; event attributes use a fixed source allowlist.
        $policy = "base-uri 'self'; object-src 'none'; form-action 'self'; frame-ancestors 'self'; "
            . "script-src 'self' 'nonce-" . $nonce . "' 'unsafe-eval'; "
            . "script-src-attr 'unsafe-hashes' 'sha256-LdlORHyUW/rwezK0l13nW+IwcZmi78eWOCBjewMWRr4=' 'sha256-SacIAUo6RFYMmwPsd5WfjktyE1H6sV15Zz1d6KlEUVI=' 'sha256-NEzEZ9J4fL8pZoV/kWz1K6aIrhth4O4ByrhOZIoKvOU=' 'sha256-0vm6V6aLh6wHoH+R87JymZt/VNT4vLmMiYudyr3FRZ0=' 'sha256-or1Qi63m/FyD7i4l8THt2kfEDmOZpZXE37pHzdkxonc=' 'sha256-Z7m3qgTFOryC9uPcL5wpNYykhPwDzWH8errXU9tpCGM=' 'sha256-oQLAMthMvW1PTRiQGDeTA5mXuc06rBpn0snfFvmf/N8=' 'sha256-2rvfFrggTCtyF5WOiTri1gDS8Boibj4Njn0e+VCBmDI=' 'sha256-QPqcrSTrPt9NrDy391mQAD4zys0dysTCAHyPuNpedoE=' 'sha256-U4xx87fWPZRIdidVskMtoCt7vMOxCwjXQuk7+vhzgPo=' 'sha256-f8pM0EpuLvf0beWBTqcNiDGVFhWGtHFFl6miKbR5gQQ=' 'sha256-/umGO7dpFMYeLgLtpvnxmfKaeeqY/lpjMXYd70WPyzc=' 'sha256-7w10lXm1ESzQOQwcHT8hoMvmMHRuS5QLlNb3rGkaGiI=' 'sha256-MguIPR6qNR8D3B+eAlK+bIRTZe8t3wkOY4B/56Me9FU='";
        header('Content-Security-Policy: ' . $policy);
        header('Referrer-Policy: same-origin');
        header_remove('X-Powered-By');
    }
}
