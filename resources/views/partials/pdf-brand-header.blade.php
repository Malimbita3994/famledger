{{-- Letterhead-style header: logo.svg.png (PNG) preferred; raster needs GD. Fallback: logo.svg, then logo.png. --}}
@php
    $famledgerPdfLogoSrc = null;
    $famledgerPdfLogoSvgPng = public_path('images/logo.svg.png');
    $famledgerPdfSvgPath = public_path('images/logo.svg');
    $famledgerPdfPngPath = public_path('images/logo.png');
    if (is_file($famledgerPdfLogoSvgPng) && is_readable($famledgerPdfLogoSvgPng) && extension_loaded('gd')) {
        $famledgerPdfLogoSrc = 'data:image/png;base64,' . base64_encode((string) file_get_contents($famledgerPdfLogoSvgPng));
    } elseif (is_file($famledgerPdfSvgPath) && is_readable($famledgerPdfSvgPath)) {
        $famledgerPdfLogoSrc = 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($famledgerPdfSvgPath));
    } elseif (extension_loaded('gd') && is_file($famledgerPdfPngPath) && is_readable($famledgerPdfPngPath)) {
        $famledgerPdfLogoSrc = 'data:image/png;base64,' . base64_encode((string) file_get_contents($famledgerPdfPngPath));
    }
    $famledgerBrandLeft = 'width:70%;vertical-align:bottom;padding:0 8px 11px 12px;border-top:none;border-right:none;border-bottom:1px solid #cbd5e1;border-left:3px solid #0f766e;';
    $famledgerBrandRight = 'width:30%;vertical-align:bottom;text-align:right;padding:0 0 11px 8px;border:none;border-bottom:1px solid #cbd5e1;';
@endphp
<table class="pdf-brand-header" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin:0 0 16px 0;border:none;">
    <tr>
        <td style="{{ $famledgerBrandLeft }}">
            @if ($famledgerPdfLogoSrc)
                <img src="{{ $famledgerPdfLogoSrc }}" alt="" style="height:26px;width:auto;max-height:26px;vertical-align:middle;display:inline-block;" />
            @endif
            <span style="font-size:14px;font-weight:bold;color:#0f172a;vertical-align:middle;letter-spacing:-0.03em;{{ $famledgerPdfLogoSrc ? 'margin-left:9px;' : '' }}">FamLedger</span>
        </td>
        <td style="{{ $famledgerBrandRight }}">
            <span style="font-size:7px;color:#64748b;text-transform:uppercase;letter-spacing:0.12em;line-height:1.4;">Family finance</span>
        </td>
    </tr>
</table>
