<?php

return [
    'request_token' => env('HIRING_REQUEST_TOKEN'),

    'ocr' => [
        'enabled' => env('CV_OCR_ENABLED', true),
        'max_pages' => (int) env('CV_OCR_MAX_PAGES', 3),
        'dpi' => (int) env('CV_OCR_DPI', 200),
        'languages' => env('CV_OCR_LANGUAGES', 'ind+eng'),
        'binaries' => [
            'pdftotext' => env('CV_PDFTOTEXT_BIN'),
            'pdftoppm' => env('CV_PDFTOPPM_BIN'),
            'gswin64c' => env('CV_GHOSTSCRIPT_BIN'),
            'tesseract' => env('CV_TESSERACT_BIN'),
            'soffice' => env('CV_SOFFICE_BIN'),
            'antiword' => env('CV_ANTIWORD_BIN'),
        ],
    ],
];
