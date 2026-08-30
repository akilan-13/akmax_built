@extends('layouts/blankLayout')

@section('title', 'EGC Job Interview')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/dropzone/dropzone.scss',
    'resources/assets/vendor/libs/tagify/tagify.scss',
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
    'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/dropzone/dropzone.js',
])
@endsection

@section('page-script')
@vite(['resources/assets/js/forms_date_time_pickers.js'])
@endsection

<style>
    :root {
        --iv-primary: #696cff;
        --iv-primary-dark: #5f61e8;
        --iv-success: #28c76f;
        --iv-danger: #ea5455;
        --iv-warning: #ff9f43;
        --iv-text: #2f3349;
        --iv-muted: #6e6b7b;
        --iv-border: #e7e7ed;
        --iv-surface: #fff;
        --iv-page: #f6f6f9;
        --iv-radius: 18px;
        --iv-shadow: 0 12px 35px rgba(47, 43, 61, .08);
    }

    html,
    body {
        min-height: 100%;
    }

    body {
        margin: 0;
        background: var(--iv-page);
        color: var(--iv-text);
        font-family: 'Roboto', sans-serif;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    body.iv-interview-active {
        overflow-x: hidden;
    }

    button,
    textarea,
    video,
    audio {
        -webkit-tap-highlight-color: transparent;
    }

    button:focus-visible,
    textarea:focus-visible {
        outline: 3px solid rgba(105, 108, 255, .22);
        outline-offset: 2px;
    }

    .interview-page {
        width: min(100%, 1100px);
        margin: 0 auto;
        padding: clamp(12px, 2vw, 28px);
    }

    .interview-shell {
        width: 100%;
    }

    .interview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .brand-logo {
        display: block;
        width: auto;
        max-width: 150px;
        max-height: 42px;
        object-fit: contain;
    }

    .secure-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 32px;
        padding: 6px 10px;
        border: 1px solid #dfe0f5;
        border-radius: 999px;
        background: #fff;
        color: #5d5f72;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .question-section {
        margin-bottom: 14px;
        padding: clamp(14px, 2vw, 20px);
        border: 1px solid rgba(105, 108, 255, .10);
        border-radius: var(--iv-radius);
        background: rgba(255, 255, 255, .92);
        box-shadow: var(--iv-shadow);
    }

    .question-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
    }

    .question-count {
        color: var(--iv-primary);
        font-size: 13px;
        font-weight: 700;
    }

    .question-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--iv-muted);
        font-size: 12px;
        font-weight: 500;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--iv-warning);
        box-shadow: 0 0 0 4px rgba(255, 159, 67, .13);
    }

    .status-dot.is-live {
        background: var(--iv-danger);
        box-shadow: 0 0 0 4px rgba(234, 84, 85, .13);
    }

    .status-dot.is-ready {
        background: var(--iv-success);
        box-shadow: 0 0 0 4px rgba(40, 199, 111, .13);
    }

    .question-text {
        margin: 0;
        color: #242637;
        font-size: clamp(16px, 2vw, 20px);
        line-height: 1.5;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .question-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 14px;
        margin: 12px 0 0;
        color: var(--iv-muted);
        font-size: 12px;
    }

    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .answer-card {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--iv-border);
        border-radius: var(--iv-radius);
        background: var(--iv-surface);
        box-shadow: var(--iv-shadow);
    }

    .answer-stage {
        position: relative;
        width: 100%;
        min-height: 300px;
    }

    #text_interview,
    #audio_interview,
    #video_interview {
        width: 100%;
    }

    #text_interview {
        padding: 14px;
    }

    .answer-textarea {
        display: block;
        width: 100%;
        min-height: clamp(250px, 45vh, 480px);
        padding: 16px;
        border: 1px solid var(--iv-border) !important;
        border-radius: 14px !important;
        outline: none;
        resize: vertical;
        color: var(--iv-text);
        background: #fff;
        font-size: 15px;
        line-height: 1.65;
        box-shadow: none;
    }

    .answer-textarea:disabled {
        cursor: not-allowed;
        background: #f8f8fb;
    }

    .text-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 9px 2px 0;
    }

    .char-count {
        color: #9b99a5;
        font-size: 12px;
    }

    /* ---------------- VIDEO ---------------- */
    #video_interview {
        position: relative;
        display: none;
        background: #090a0f;
    }

    .video-stage {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: min(62vh, 620px);
        max-height: 78vh;
        overflow: hidden;
        background: #090a0f;
    }

    #video_interview video {
        display: block;
        width: 100%;
        height: auto;
        max-width: 100%;
        max-height: 78vh;
        aspect-ratio: auto;
        object-fit: contain;
        background: #090a0f;
        border-radius: 0;
    }

    /* When the camera reports portrait dimensions, the full portrait frame is
       intentionally preserved instead of being cropped into 16:9. */
    #video_interview.is-portrait .video-stage {
        min-height: min(82vh, 760px);
        max-height: 86vh;
    }

    #video_interview.is-portrait #preview,
    #video_interview.is-portrait #recorded {
        width: auto;
        height: min(82vh, 760px);
        max-width: 100%;
        max-height: 86vh;
        object-fit: contain;
    }

    #video_interview.is-landscape #preview,
    #video_interview.is-landscape #recorded {
        width: 100%;
        height: auto;
        max-height: 78vh;
        object-fit: contain;
    }

    .video-overlay-top {
        position: absolute;
        top: 12px;
        left: 12px;
        right: 12px;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        pointer-events: none;
    }

    .recording-pill,
    .camera-mode-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(0, 0, 0, .64);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .recording-pill {
        opacity: 0;
        transition: opacity .2s ease;
    }

    .recording-pill.is-visible {
        opacity: 1;
    }

    .recording-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ff4d5d;
        box-shadow: 0 0 0 4px rgba(255, 77, 93, .18);
        animation: recordingPulse 1.25s infinite;
    }

    @keyframes recordingPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .42; }
    }

    .video-fullscreen-btn {
        position: absolute;
        right: 12px;
        bottom: 12px;
        z-index: 6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        padding: 0;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 10px;
        background: rgba(0,0,0,.58);
        color: #fff;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* ---------------- AUDIO ---------------- */
    #audio_interview {
        display: none;
        padding: clamp(14px, 3vw, 24px);
        background: #11131a;
    }

    #audio_container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
        min-height: clamp(260px, 42vh, 430px);
    }

    #preview_audio {
        display: none;
    }

    #audioWaveform {
        display: block;
        width: 100%;
        height: clamp(130px, 24vw, 190px);
        border-radius: 16px;
        background: linear-gradient(180deg, #0b0c11, #181a23);
    }

    #audio_container audio {
        width: 100%;
        border-radius: 12px;
    }

    .audio-state {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #d5d6df;
        font-size: 13px;
        text-align: center;
    }

    /* ---------------- THINKING OVERLAY ---------------- */
    .thinking-time-container {
        position: absolute;
        inset: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border-radius: inherit;
        background: rgba(7, 8, 12, .96);
    }

    .thinking-time {
        position: relative;
        width: 136px;
        height: 136px;
    }

    .progress-ring {
        display: block;
        width: 136px;
        height: 136px;
    }

    .progress-ring__circle {
        transition: stroke-dashoffset .9s linear;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }

    .thinking-time-text {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffb84a;
        font-weight: 700;
    }

    .thinking-time-text small {
        color: #a7a8b1;
        font-size: 11px;
        font-weight: 500;
    }

    .thinking-time-text span {
        font-size: 34px;
        line-height: 1.15;
    }

    /* ---------------- CONTROLS ---------------- */
    .interview-controls {
        margin-top: 14px;
    }

    .timer-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        margin-bottom: 10px;
        padding: 8px 12px;
        border: 1px solid var(--iv-border);
        border-radius: 12px;
        background: #fff;
        color: var(--iv-muted);
        font-size: 13px;
    }

    .timer-value {
        min-width: 64px;
        color: var(--iv-text);
        font-variant-numeric: tabular-nums;
        font-weight: 800;
        letter-spacing: .03em;
    }

    .timer-bar.is-warning {
        border-color: rgba(234, 84, 85, .25);
        color: var(--iv-danger);
    }

    .timer-bar.is-warning .timer-value {
        color: var(--iv-danger);
    }

    .action-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 9px;
    }

    .action-btn {
        min-height: 48px;
        border-radius: 12px !important;
        font-size: 14px;
        font-weight: 700 !important;
    }

    .btn-primary.action-btn {
        box-shadow: 0 7px 16px rgba(105,108,255,.18);
    }

    .retake-info {
        margin-top: 9px;
        padding: 10px 12px;
        border-radius: 11px;
        background: #fff1f1;
        color: #9f3f40;
        font-size: 12px;
        line-height: 1.5;
    }

    /* ---------------- MODALS ---------------- */
    .iv-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(19, 20, 28, .62);
        backdrop-filter: blur(7px);
        -webkit-backdrop-filter: blur(7px);
    }

    .iv-modal-card {
        width: min(100%, 480px);
        max-height: min(90vh, 720px);
        overflow: auto;
        padding: clamp(20px, 4vw, 30px);
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 25px 80px rgba(0,0,0,.25);
    }

    .system-check-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .system-check-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin-bottom: 10px;
        border-radius: 16px;
        background: #efefff;
        color: var(--iv-primary);
        font-size: 25px;
    }

    .system-check-header h4 {
        margin: 0;
        color: #282a3b;
        font-weight: 800;
    }

    .system-check-header p {
        margin: 7px 0 0;
        color: var(--iv-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .check-item {
        display: flex;
        align-items: center;
        gap: 11px;
        min-height: 54px;
        margin-bottom: 8px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 12px;
        background: #f8f8fb;
        transition: background .2s ease, border-color .2s ease;
    }

    .check-item.is-success {
        background: #edfaf3;
        border-color: #c9efd9;
    }

    .check-item.is-error {
        background: #fff1f1;
        border-color: #f5cccc;
    }

    .check-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
    }

    .check-label {
        min-width: 0;
        color: #36384a;
        font-size: 13px;
        font-weight: 600;
    }

    .check-detail {
        display: block;
        margin-top: 2px;
        color: #8a8794;
        font-size: 11px;
        font-weight: 400;
    }

    .failure-block {
        margin-top: 14px;
    }

    .iv-modal-actions {
        display: grid;
        gap: 8px;
        margin-top: 14px;
    }

    .upload-card {
        width: min(100%, 360px);
        text-align: center;
    }

    .upload-spinner {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        margin-bottom: 12px;
        border-radius: 15px;
        background: #fff6e8;
        color: var(--iv-warning);
        font-size: 22px;
    }

    .upload-title {
        margin: 0 0 5px;
        font-weight: 800;
    }

    .upload-message {
        margin: 0 0 14px;
        color: var(--iv-muted);
        font-size: 12px;
    }

    .progress {
        height: 7px;
        overflow: hidden;
        border-radius: 99px;
        background: #eeeef4;
    }

    .system-alert-card {
        width: min(100%, 420px);
        text-align: center;
    }

    .alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: #fff6e8;
        font-size: 25px;
    }

    .system-alert-card h5 {
        margin: 12px 0 0;
        font-weight: 800;
    }

    .system-alert-card p {
        margin: 8px 0 0;
        color: var(--iv-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .network-banner {
        display: none;
        margin-bottom: 10px;
        padding: 10px 12px;
        border: 1px solid #f2cccc;
        border-radius: 11px;
        background: #fff1f1;
        color: #a43e40;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
    }

    .network-banner.is-visible {
        display: block;
    }

    .sr-only {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    @media (min-width: 768px) {
        .action-row {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .action-row.single-action {
            grid-template-columns: minmax(0, 1fr);
        }

        .answer-textarea {
            min-height: 360px;
        }
    }

    @media (max-width: 767px) {
        .interview-page {
            padding: 10px;
        }

        .interview-header {
            margin-bottom: 10px;
        }

        .brand-logo {
            max-width: 120px;
            max-height: 36px;
        }

        .secure-badge {
            min-height: 29px;
            padding: 5px 8px;
            font-size: 10px;
        }

        .question-section {
            padding: 13px;
            border-radius: 15px;
        }

        .question-topline {
            align-items: flex-start;
        }

        .question-text {
            font-size: 16px;
        }

        .answer-card {
            border-radius: 15px;
        }

        .answer-stage {
            min-height: 260px;
        }

        .video-stage {
            min-height: 48vh;
            max-height: none;
        }

        #video_interview.is-portrait .video-stage {
            min-height: min(78vh, 720px);
            max-height: 82vh;
        }

        #video_interview.is-portrait #preview,
        #video_interview.is-portrait #recorded {
            height: min(78vh, 720px);
            max-height: 82vh;
            max-width: 100%;
        }

        #video_interview.is-landscape #preview,
        #video_interview.is-landscape #recorded {
            max-height: 62vh;
        }

        #audio_container {
            min-height: 280px;
        }

        .thinking-time-container {
            border-radius: 0;
        }

        .action-row {
            grid-template-columns: 1fr;
        }

        .action-btn {
            min-height: 50px;
        }
    }

    @media (max-width: 380px) {
        .question-meta {
            gap: 5px 9px;
        }

        .question-text {
            font-size: 15px;
        }

        .timer-bar {
            font-size: 12px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>

@section('content')
<div class="interview-page" id="interview_page" style="display:none;">
    <div class="interview-shell">
        <header class="interview-header" aria-label="Interview header">
            <img src="{{ asset('assets/common/logo_full.png') }}" alt="Company Logo" class="brand-logo">
            <div class="secure-badge" title="Interview session is protected by server-side session validation">
                <span class="mdi mdi-shield-check-outline" aria-hidden="true"></span>
                Secure interview
            </div>
        </header>

        <div id="networkBanner" class="network-banner" role="alert">
            <span class="mdi mdi-wifi-off me-1" aria-hidden="true"></span>
            Internet connection lost. Keep this page open and reconnect before submitting.
        </div>

        <section class="question-section" aria-live="polite">
            <div class="question-topline">
                <span class="question-count" id="questionCount">Question 1 of 1</span>
                <span class="question-status">
                    <span class="status-dot" id="questionStatusDot"></span>
                    <span id="questionStatusText">Preparing</span>
                </span>
            </div>

            <p class="question-text" id="questionText">
                Loading interview question…
            </p>

            <div class="question-meta" aria-label="Question details">
                <span class="meta-item" id="descriptionMeta">
                    <span class="mdi mdi-information-outline" aria-hidden="true"></span>
                    <span id="questionDescription">No description</span>
                </span>
                <span class="meta-item">
                    <span class="mdi mdi-microphone-message-outline" aria-hidden="true"></span>
                    <span id="answerTypeLabel">Response</span>
                </span>
                <span class="meta-item">
                    <span class="mdi mdi-timer-outline" aria-hidden="true"></span>
                    <span><span id="allow_time">00:00</span> answer time</span>
                </span>
                <span class="meta-item">
                    <span class="mdi mdi-refresh" aria-hidden="true"></span>
                    <span><span id="retake_count">0</span> retakes</span>
                </span>
            </div>
        </section>

        <section class="answer-card" aria-label="Interview answer area">
            <div class="answer-stage">
                <div id="text_interview" style="display:none;">
                    <label for="answerTextarea_1" class="sr-only">Interview answer</label>
                    <textarea
                        class="answer-textarea"
                        id="answerTextarea_1"
                        maxlength="1000"
                        placeholder="Type your answer here…"
                        autocomplete="off"
                        autocapitalize="sentences"
                        spellcheck="true"
                        disabled></textarea>
                    <div class="text-footer">
                        <span class="char-count" id="charCount">0/1000</span>
                    </div>
                </div>

                <div id="video_interview" style="display:none;" aria-label="Video recording panel">
                    <div class="video-stage" id="videoStage">
                        <video id="preview" autoplay muted playsinline preload="none"></video>
                        <video id="recorded" controls playsinline preload="metadata" class="d-none"></video>
                    </div>
                    <div class="video-overlay-top">
                        <span class="recording-pill" id="recordingPill">
                            <span class="recording-dot" aria-hidden="true"></span>
                            Recording
                        </span>
                        <span class="camera-mode-pill" id="cameraModePill">Camera</span>
                    </div>
                    <button type="button" class="video-fullscreen-btn" id="videoFullscreenBtn" aria-label="Open video fullscreen" title="Fullscreen">
                        <span class="mdi mdi-fullscreen" aria-hidden="true"></span>
                    </button>
                </div>

                <div id="audio_interview" style="display:none;" aria-label="Audio recording panel">
                    <div id="audio_container">
                        <div class="audio-state" id="audioState">
                            <span class="mdi mdi-microphone-outline" aria-hidden="true"></span>
                            <span>Microphone ready</span>
                        </div>
                        <canvas id="audioWaveform" width="1200" height="240" aria-label="Live audio waveform"></canvas>
                        <audio id="preview_audio" autoplay muted></audio>
                        <audio id="recorded_audio" controls class="d-none" preload="metadata"></audio>
                    </div>
                </div>

                <div id="thinkingOverlay" class="thinking-time-container" style="display:none;" aria-live="assertive">
                    <div class="thinking-time">
                        <svg class="progress-ring" viewBox="0 0 136 136" aria-hidden="true">
                            <circle stroke="#30323c" stroke-width="8" fill="transparent" r="56" cx="68" cy="68"></circle>
                            <circle
                                id="thinkingProgressCircle"
                                class="progress-ring__circle"
                                stroke="#ffb84a"
                                stroke-width="8"
                                fill="transparent"
                                r="56"
                                cx="68"
                                cy="68"></circle>
                        </svg>
                        <div class="thinking-time-text">
                            <small>Starts in</small>
                            <span id="thinkingTimer">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="interview-controls" aria-label="Interview controls">
            <div class="timer-bar" id="timerBar">
                <span class="mdi mdi-timer-outline" aria-hidden="true"></span>
                <span>Remaining time</span>
                <strong class="timer-value" id="remaining_time">00:00</strong>
            </div>

            <div class="action-row" id="actionRow">
                <button type="button" class="btn btn-danger action-btn d-none" id="recordStopBtn">
                    <span class="mdi mdi-stop-circle-outline me-1" aria-hidden="true"></span>
                    Stop recording
                </button>

                <button type="button" class="btn btn-warning action-btn d-none" id="recordResetBtn">
                    <span class="mdi mdi-refresh me-1" aria-hidden="true"></span>
                    Retake
                </button>

                <button type="button" class="btn btn-primary action-btn" id="questionBtn" disabled>
                    <span class="mdi mdi-play-circle-outline me-1" aria-hidden="true"></span>
                    <span id="ans_submit_btn">Start</span>
                </button>
            </div>

            <div class="retake-info d-none" id="resetInfo">
                <span class="mdi mdi-alert-circle-outline me-1" aria-hidden="true"></span>
                This is your final retake. Take a breath and give it your best.
            </div>
        </section>
    </div>
</div>

<div id="permissionModal" class="iv-modal" role="dialog" aria-modal="true" aria-labelledby="systemCheckTitle">
    <div class="iv-modal-card">
        <div class="system-check-header">
            <div class="system-check-icon">
                <span class="mdi mdi-shield-check-outline" aria-hidden="true"></span>
            </div>
            <h4 id="systemCheckTitle">System check</h4>
            <p id="systemCheckDescription">We’ll verify the requirements needed for this interview before you begin.</p>
        </div>

        <div class="check-item" id="check_net">
            <div class="check-status" aria-hidden="true">
                <span class="spinner-border spinner-border-sm text-primary check-spinner"></span>
                <i class="mdi mdi-check-circle text-success fs-4 d-none check-success"></i>
                <i class="mdi mdi-close-circle text-danger fs-4 d-none check-error"></i>
            </div>
            <div class="check-label">
                Internet connection
                <span class="check-detail" id="check_net_detail">Checking connection…</span>
            </div>
        </div>

        <div class="check-item d-none" id="check_cam">
            <div class="check-status" aria-hidden="true">
                <span class="spinner-border spinner-border-sm text-primary check-spinner"></span>
                <i class="mdi mdi-check-circle text-success fs-4 d-none check-success"></i>
                <i class="mdi mdi-close-circle text-danger fs-4 d-none check-error"></i>
            </div>
            <div class="check-label">
                Camera access
                <span class="check-detail" id="check_cam_detail">Checking camera permission…</span>
            </div>
        </div>

        <div class="check-item d-none" id="check_mic">
            <div class="check-status" aria-hidden="true">
                <span class="spinner-border spinner-border-sm text-primary check-spinner"></span>
                <i class="mdi mdi-check-circle text-success fs-4 d-none check-success"></i>
                <i class="mdi mdi-close-circle text-danger fs-4 d-none check-error"></i>
            </div>
            <div class="check-label">
                Microphone access
                <span class="check-detail" id="check_mic_detail">Checking microphone permission…</span>
            </div>
        </div>

        <div id="failureBlock" class="failure-block d-none">
            <div class="alert alert-danger small py-2 mb-0" id="failureMessage">
                Verification failed. Please check your browser permissions and try again.
            </div>
            <div class="iv-modal-actions">
                <button type="button" class="btn btn-primary action-btn" id="retrySystemCheckBtn">Try again</button>
            </div>
        </div>
    </div>
</div>

<div id="uploadModal" class="iv-modal" role="dialog" aria-modal="true" aria-labelledby="uploadTitle">
    <div class="iv-modal-card upload-card">
        <div class="upload-spinner">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        </div>
        <h5 class="upload-title" id="uploadTitle">Saving your answer…</h5>
        <p class="upload-message" id="uploadMessage">Please keep this page open until the upload finishes.</p>
        <div class="progress w-100" aria-label="Upload progress">
            <div id="uploadProgress" class="progress-bar bg-warning" style="width:0%" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
        </div>
    </div>
</div>

<div id="systemAlertModal" class="iv-modal" role="dialog" aria-modal="true" aria-labelledby="alertTitle">
    <div class="iv-modal-card system-alert-card">
        <div class="alert-icon" id="alertIcon">⚠️</div>
        <h5 id="alertTitle">Attention</h5>
        <p id="alertMessage">Please review the message before continuing.</p>
        <div class="iv-modal-actions">
            <button type="button" class="btn btn-primary action-btn" id="closeSystemAlertBtn">I understand</button>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';

    /*
     * IMPORTANT SECURITY NOTE:
     * Browser-side controls are UX/integrity signals only. They are NOT a
     * security boundary. The backend must validate session_token, question_id,
     * answer ownership, retake limits, timing and completion state again.
     */

    const SESSION_TOKEN = @json($session->session_token ?? null);
    const RAW_INTERVIEW_TYPE = @json($session->interview_mode_name ?? 'Assesment');
    const INTERVIEW_TYPE = String(RAW_INTERVIEW_TYPE || 'Assesment').trim().toLowerCase();

    const MODE = {
        TEXT: ['assessment', 'assesment', 'text'].includes(INTERVIEW_TYPE),
        VIDEO: INTERVIEW_TYPE === 'video',
        AUDIO: INTERVIEW_TYPE === 'audio'
    };

    const state = {
        isSubmitting: false,
        isLoadingQuestion: false,
        systemCheckRunning: false,
        hasStarted: false,
        isRecording: false,
        isAutoStop: false,
        currentQuestion: null,
        totalQuestions: 0,
        currentQuestionNumber: 0,
        thinkingSeconds: 0,
        answerSeconds: 0,
        remainingSeconds: 0,
        remainingRetakes: 0,
        originalRetakes: 0,
        stream: null,
        recorder: null,
        chunks: [],
        recordedUrl: null,
        thinkingTimerId: null,
        answerTimerId: null,
        waveformRaf: null,
        audioCtx: null,
        analyser: null,
        micSource: null,
        tabSwitchCount: 0,
        xhr: null,
        uploadRetryAvailable: false,
        systemReady: false,
        mediaPermissionGranted: false,
        beforeUnloadEnabled: false,
        currentMimeType: ''
    };

    const $ = (id) => document.getElementById(id);

    const els = {
        page: $('interview_page'),
        modal: $('permissionModal'),
        failureBlock: $('failureBlock'),
        failureMessage: $('failureMessage'),
        retrySystemCheckBtn: $('retrySystemCheckBtn'),
        questionCount: $('questionCount'),
        questionText: $('questionText'),
        questionDescription: $('questionDescription'),
        answerTypeLabel: $('answerTypeLabel'),
        allowTime: $('allow_time'),
        retakeCount: $('retake_count'),
        questionStatusText: $('questionStatusText'),
        questionStatusDot: $('questionStatusDot'),
        textInterview: $('text_interview'),
        videoInterview: $('video_interview'),
        audioInterview: $('audio_interview'),
        videoStage: $('videoStage'),
        preview: $('preview'),
        recorded: $('recorded'),
        previewAudio: $('preview_audio'),
        recordedAudio: $('recorded_audio'),
        audioState: $('audioState'),
        waveform: $('audioWaveform'),
        thinkingOverlay: $('thinkingOverlay'),
        thinkingTimer: $('thinkingTimer'),
        thinkingCircle: $('thinkingProgressCircle'),
        remaining: $('remaining_time'),
        timerBar: $('timerBar'),
        questionBtn: $('questionBtn'),
        submitLabel: $('ans_submit_btn'),
        recordStopBtn: $('recordStopBtn'),
        recordResetBtn: $('recordResetBtn'),
        resetInfo: $('resetInfo'),
        actionRow: $('actionRow'),
        charCount: $('charCount'),
        textarea: $('answerTextarea_1'),
        recordingPill: $('recordingPill'),
        cameraModePill: $('cameraModePill'),
        videoFullscreenBtn: $('videoFullscreenBtn'),
        uploadModal: $('uploadModal'),
        uploadProgress: $('uploadProgress'),
        uploadMessage: $('uploadMessage'),
        alertModal: $('systemAlertModal'),
        alertIcon: $('alertIcon'),
        alertTitle: $('alertTitle'),
        alertMessage: $('alertMessage'),
        closeAlertBtn: $('closeSystemAlertBtn'),
        networkBanner: $('networkBanner')
    };

    const CIRCUMFERENCE = 2 * Math.PI * 56;
    els.thinkingCircle.style.strokeDasharray = `${CIRCUMFERENCE}`;
    els.thinkingCircle.style.strokeDashoffset = `${CIRCUMFERENCE}`;

    function normalizeModeLabel() {
        if (MODE.VIDEO) return 'Video response';
        if (MODE.AUDIO) return 'Audio response';
        return 'Text response';
    }

    function parseTimeString(value) {
        if (value === null || value === undefined || value === '') return 0;
        if (typeof value === 'number' && Number.isFinite(value)) return Math.max(0, Math.floor(value));

        const str = String(value).trim();
        if (/^\d+(\.\d+)?$/.test(str)) return Math.max(0, Math.floor(Number(str)));

        let seconds = 0;
        const hour = str.match(/(\d+(?:\.\d+)?)\s*hours?/i);
        const min = str.match(/(\d+(?:\.\d+)?)\s*minutes?/i);
        const sec = str.match(/(\d+(?:\.\d+)?)\s*seconds?/i);
        if (hour) seconds += Number(hour[1]) * 3600;
        if (min) seconds += Number(min[1]) * 60;
        if (sec) seconds += Number(sec[1]);
        return Math.max(0, Math.floor(seconds));
    }

    function formatTime(seconds) {
        const total = Math.max(0, Math.floor(Number(seconds) || 0));
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;
        return h > 0
            ? `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
            : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function setQuestionStatus(label, type = 'ready') {
        els.questionStatusText.textContent = label;
        els.questionStatusDot.classList.remove('is-live', 'is-ready');
        if (type === 'live') els.questionStatusDot.classList.add('is-live');
        if (type === 'ready') els.questionStatusDot.classList.add('is-ready');
    }

    function setButtonLabel(label) {
        els.submitLabel.textContent = label;
        els.questionBtn.setAttribute('aria-label', label);
    }

    function updateActionLayout() {
        const visible = [els.recordStopBtn, els.recordResetBtn, els.questionBtn]
            .filter(el => !el.classList.contains('d-none')).length;
        els.actionRow.classList.toggle('single-action', visible <= 1);
    }

    function setQuestionButtonEnabled(enabled) {
        els.questionBtn.disabled = !enabled || state.isSubmitting || state.isLoadingQuestion;
    }

    function stopAllTimers() {
        if (state.thinkingTimerId) clearInterval(state.thinkingTimerId);
        if (state.answerTimerId) clearInterval(state.answerTimerId);
        state.thinkingTimerId = null;
        state.answerTimerId = null;
    }

    function setBeforeUnloadGuard(enabled) {
        state.beforeUnloadEnabled = Boolean(enabled);
        window.onbeforeunload = enabled ? (event) => {
            event.preventDefault();
            event.returnValue = '';
            return '';
        } : null;
    }

    function setModal(modal, visible) {
        if (!modal) return;
        modal.style.display = visible ? 'flex' : 'none';
    }

    function showSystemAlert({ title, message, type = 'warning' }) {
        els.alertTitle.textContent = title || 'Attention';
        els.alertMessage.textContent = message || 'Please review this message.';
        els.alertIcon.textContent = type === 'error' ? '❌' : type === 'info' ? 'ℹ️' : type === 'success' ? '✅' : '⚠️';
        setModal(els.alertModal, true);
    }

    function closeSystemAlert() {
        setModal(els.alertModal, false);
    }

    function updateRemainingTime(seconds) {
        state.remainingSeconds = Math.max(0, Math.floor(seconds));
        els.remaining.textContent = formatTime(state.remainingSeconds);
        els.timerBar.classList.toggle('is-warning', state.remainingSeconds > 0 && state.remainingSeconds <= 10);
    }

    function setThinkingProgress(current, total) {
        if (!total) {
            els.thinkingCircle.style.strokeDashoffset = `${CIRCUMFERENCE}`;
            return;
        }
        const ratio = Math.min(1, Math.max(0, current / total));
        els.thinkingCircle.style.strokeDashoffset = `${CIRCUMFERENCE - ratio * CIRCUMFERENCE}`;
    }

    function cleanupRecordedUrl() {
        if (state.recordedUrl) {
            try { URL.revokeObjectURL(state.recordedUrl); } catch (_) {}
            state.recordedUrl = null;
        }
    }

    function stopWaveform() {
        if (state.waveformRaf) cancelAnimationFrame(state.waveformRaf);
        state.waveformRaf = null;
        if (els.waveform) {
            const ctx = els.waveform.getContext('2d');
            ctx.clearRect(0, 0, els.waveform.width, els.waveform.height);
        }
    }

    function disconnectAudioEngine() {
        stopWaveform();
        if (state.micSource) {
            try { state.micSource.disconnect(); } catch (_) {}
            state.micSource = null;
        }
        if (state.audioCtx) {
            try { state.audioCtx.close(); } catch (_) {}
            state.audioCtx = null;
            state.analyser = null;
        }
    }

    function resetVideoUI() {
        els.videoInterview.classList.remove('is-portrait', 'is-landscape');
        els.cameraModePill.textContent = 'Camera';
        els.recordingPill.classList.remove('is-visible');
        els.preview.classList.remove('d-none');
        els.recorded.classList.add('d-none');
        els.preview.removeAttribute('src');
        els.recorded.removeAttribute('src');
        els.preview.srcObject = null;
    }

    function removeRecordedPreview() {
        els.recorded.pause();
        els.recorded.removeAttribute('src');
        els.recorded.load();
        els.recorded.classList.add('d-none');

        if (els.recordedAudio) {
            els.recordedAudio.pause();
            els.recordedAudio.removeAttribute('src');
            els.recordedAudio.load();
            els.recordedAudio.classList.add('d-none');
        }
        cleanupRecordedUrl();
    }

    function stopMediaStream() {
        if (state.stream) {
            state.stream.getTracks().forEach(track => {
                try { track.stop(); } catch (_) {}
            });
            state.stream = null;
        }
        state.isRecording = false;
        els.recordingPill.classList.remove('is-visible');
    }

    function cleanupMedia({ keepRecorded = false } = {}) {
        stopWaveform();
        stopMediaStream();
        disconnectAudioEngine();

        if (!keepRecorded) removeRecordedPreview();

        els.preview.pause();
        els.preview.srcObject = null;
        els.preview.classList.add('d-none');

        els.previewAudio.pause();
        els.previewAudio.srcObject = null;

        state.recorder = null;
        state.chunks = [];
        state.currentMimeType = '';
    }

    function updateRetakeUI() {
        els.retakeCount.textContent = String(Math.max(0, state.remainingRetakes));
        const showRetake = !state.isRecording && state.hasStarted && !!state.recordedUrl && state.remainingRetakes > 0 && (MODE.VIDEO || MODE.AUDIO);
        els.recordResetBtn.classList.toggle('d-none', !showRetake);
        els.resetInfo.classList.toggle('d-none', !(state.hasStarted && (MODE.VIDEO || MODE.AUDIO) && state.remainingRetakes <= 0 && !!state.recordedUrl));
        updateActionLayout();
    }

    function resetQuestionUI() {
        stopAllTimers();
        cleanupMedia();
        state.hasStarted = false;
        state.isRecording = false;
        state.isAutoStop = false;
        state.chunks = [];
        state.originalRetakes = state.remainingRetakes;
        state.recordedUrl = null;

        els.thinkingOverlay.style.display = 'none';
        els.recordStopBtn.classList.add('d-none');
        els.recordResetBtn.classList.add('d-none');
        els.resetInfo.classList.add('d-none');
        els.textarea.disabled = true;
        els.textarea.value = '';
        els.charCount.textContent = '0/1000';
        updateRemainingTime(state.answerSeconds);
        setButtonLabel('Start');
        setQuestionButtonEnabled(false);
        resetVideoUI();
        updateActionLayout();
    }

    function prepareModeUI() {
        els.textInterview.style.display = MODE.TEXT ? 'block' : 'none';
        els.videoInterview.style.display = MODE.VIDEO ? 'block' : 'none';
        els.audioInterview.style.display = MODE.AUDIO ? 'block' : 'none';
        els.answerTypeLabel.textContent = normalizeModeLabel();
    }

    function updateVideoOrientation() {
        if (!MODE.VIDEO || !els.preview.videoWidth || !els.preview.videoHeight) return;
        const portrait = els.preview.videoHeight > els.preview.videoWidth;
        els.videoInterview.classList.toggle('is-portrait', portrait);
        els.videoInterview.classList.toggle('is-landscape', !portrait);
        els.cameraModePill.textContent = portrait ? 'Portrait camera' : 'Landscape camera';
    }

    function getSupportedMimeType() {
        if (!window.MediaRecorder || !MediaRecorder.isTypeSupported) return '';
        const candidates = MODE.VIDEO
            ? [
                'video/webm;codecs=vp9,opus',
                'video/webm;codecs=vp8,opus',
                'video/webm'
            ]
            : [
                'audio/webm;codecs=opus',
                'audio/webm'
            ];
        return candidates.find(type => MediaRecorder.isTypeSupported(type)) || '';
    }

    function mediaConstraints() {
        if (MODE.VIDEO) {
            return {
                video: {
                    facingMode: { ideal: 'user' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    channelCount: 1
                }
            };
        }

        return {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 1
            }
        };
    }

    function drawWaveform() {
        if (!state.analyser || !els.waveform) return;
        if (state.waveformRaf) cancelAnimationFrame(state.waveformRaf);

        const canvas = els.waveform;
        const ctx = canvas.getContext('2d');
        const bufferLength = state.analyser.fftSize;
        const data = new Uint8Array(bufferLength);

        const draw = () => {
            if (!state.analyser) return;
            state.waveformRaf = requestAnimationFrame(draw);
            state.analyser.getByteTimeDomainData(data);

            const ratio = window.devicePixelRatio || 1;
            const cssWidth = canvas.clientWidth || 600;
            const cssHeight = canvas.clientHeight || 160;
            const targetWidth = Math.floor(cssWidth * ratio);
            const targetHeight = Math.floor(cssHeight * ratio);
            if (canvas.width !== targetWidth || canvas.height !== targetHeight) {
                canvas.width = targetWidth;
                canvas.height = targetHeight;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = Math.max(2, ratio * 2);
            ctx.strokeStyle = '#ffb84a';
            ctx.beginPath();

            const sliceWidth = canvas.width / bufferLength;
            let x = 0;
            for (let i = 0; i < bufferLength; i++) {
                const v = data[i] / 128 - 1;
                const y = (v * canvas.height) / 2 + canvas.height / 2;
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
                x += sliceWidth;
            }
            ctx.stroke();
        };
        draw();
    }

    async function initAudioEngine() {
        if (!state.audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return false;
            state.audioCtx = new AudioContextClass({ latencyHint: 'interactive' });
            state.analyser = state.audioCtx.createAnalyser();
            state.analyser.fftSize = 512;
            state.analyser.smoothingTimeConstant = .72;
        }
        if (state.audioCtx.state === 'suspended') {
            try { await state.audioCtx.resume(); } catch (_) {}
        }
        return true;
    }

    async function attachAudioMonitor(stream) {
        if (!MODE.AUDIO) return;
        const ready = await initAudioEngine();
        if (!ready || !state.audioCtx || !state.analyser) return;

        if (state.micSource) {
            try { state.micSource.disconnect(); } catch (_) {}
        }
        state.micSource = state.audioCtx.createMediaStreamSource(stream);
        state.micSource.connect(state.analyser);
        drawWaveform();
    }

    function setCheckStatus(rowId, status, detail) {
        const row = $(rowId);
        if (!row) return;
        const spinner = row.querySelector('.check-spinner');
        const success = row.querySelector('.check-success');
        const error = row.querySelector('.check-error');
        const detailEl = $(`${rowId}_detail`);

        row.classList.remove('is-success', 'is-error');
        spinner.classList.add('d-none');
        success.classList.add('d-none');
        error.classList.add('d-none');

        if (status === 'loading') spinner.classList.remove('d-none');
        if (status === 'success') {
            success.classList.remove('d-none');
            row.classList.add('is-success');
        }
        if (status === 'error') {
            error.classList.remove('d-none');
            row.classList.add('is-error');
        }
        if (detailEl && detail) detailEl.textContent = detail;
    }

    function requiredMediaChecks() {
        return { camera: MODE.VIDEO, microphone: MODE.VIDEO || MODE.AUDIO };
    }

    async function checkMediaPermission(kind) {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('This browser does not support camera/microphone access.');
        }
        const constraints = kind === 'camera' ? { video: true } : { audio: true };
        let tempStream = null;
        try {
            tempStream = await navigator.mediaDevices.getUserMedia(constraints);
            return true;
        } finally {
            if (tempStream) tempStream.getTracks().forEach(track => track.stop());
        }
    }

    async function startSystemCheck() {
        if (state.systemCheckRunning) return false;
        state.systemCheckRunning = true;
        state.systemReady = false;
        setModal(els.modal, true);
        els.failureBlock.classList.add('d-none');
        els.retrySystemCheckBtn.disabled = true;

        const required = requiredMediaChecks();
        $('check_cam').classList.toggle('d-none', !required.camera);
        $('check_mic').classList.toggle('d-none', !required.microphone);

        try {
            setCheckStatus('check_net', 'loading', 'Checking connection…');
            if (!navigator.onLine) throw new Error('No internet connection is available.');
            setCheckStatus('check_net', 'success', 'Connection is available.');

            if (required.camera) {
                setCheckStatus('check_cam', 'loading', 'Allow camera access when prompted.');
                await checkMediaPermission('camera');
                setCheckStatus('check_cam', 'success', 'Camera permission granted.');
            }

            if (required.microphone) {
                setCheckStatus('check_mic', 'loading', 'Allow microphone access when prompted.');
                await checkMediaPermission('microphone');
                setCheckStatus('check_mic', 'success', 'Microphone permission granted.');
            }

            state.mediaPermissionGranted = true;
            state.systemReady = true;
            return true;
        } catch (error) {
            state.mediaPermissionGranted = false;
            state.systemReady = false;
            const message = error?.message || 'Permission check failed. Please verify your browser settings.';
            if (!navigator.onLine) {
                setCheckStatus('check_net', 'error', 'You are offline.');
            } else if (required.camera && !state.mediaPermissionGranted) {
                setCheckStatus('check_cam', 'error', 'Camera permission is required for this interview.');
            }
            els.failureMessage.textContent = message;
            els.failureBlock.classList.remove('d-none');
            return false;
        } finally {
            state.systemCheckRunning = false;
            els.retrySystemCheckBtn.disabled = false;
        }
    }

    async function beginInterviewAfterSystemCheck() {
        setModal(els.modal, false);
        els.page.style.display = 'block';
        document.body.classList.add('iv-interview-active');
        setQuestionStatus('Loading question', 'ready');
        await loadNextQuestion();
    }

    function showModeSpecificInitialState() {
        prepareModeUI();
        if (MODE.AUDIO) els.audioState.innerHTML = '<span class="mdi mdi-microphone-outline" aria-hidden="true"></span><span>Microphone ready</span>';
    }

    async function loadNextQuestion() {
        if (state.isLoadingQuestion || state.isSubmitting) return;
        state.isLoadingQuestion = true;
        setQuestionButtonEnabled(false);
        stopAllTimers();
        cleanupMedia();
        resetVideoUI();
        els.thinkingOverlay.style.display = 'none';
        setQuestionStatus('Loading question', 'ready');

        try {
            const url = `/interview/question/next?session_token=${encodeURIComponent(SESSION_TOKEN || '')}`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                cache: 'no-store'
            });

            if (!response.ok) throw new Error(`Unable to load the question (${response.status}).`);
            const data = await response.json();
            if (!data || data.error) throw new Error(data?.message || 'Invalid interview response.');

            if (data.completed) {
                finalizeInterview(data.redirect);
                return;
            }

            if (!data.question) throw new Error('Question not found.');

            state.currentQuestion = data.question;
            state.totalQuestions = Number(data.progress?.total || 0);
            state.currentQuestionNumber = Number(data.progress?.current || 1);
            state.thinkingSeconds = parseTimeString(data.question.thinking_time);
            state.answerSeconds = parseTimeString(data.question.allowed_time);
            state.remainingSeconds = state.answerSeconds;
            state.remainingRetakes = Math.max(0, parseInt(data.question.retakes ?? 0, 10) || 0);
            state.originalRetakes = state.remainingRetakes;

            els.questionCount.textContent = `Question ${state.currentQuestionNumber} of ${state.totalQuestions || '—'}`;
            els.questionText.textContent = `${state.currentQuestionNumber}). ${data.question.field_name || 'Interview question'}`;
            els.questionDescription.textContent = data.question.description || 'No description';
            els.descriptionMeta?.classList.remove('d-none');
            els.allowTime.textContent = formatTime(state.answerSeconds);
            els.retakeCount.textContent = String(state.remainingRetakes);
            updateRemainingTime(state.answerSeconds);
            resetQuestionUI();
            state.remainingRetakes = Math.max(0, parseInt(data.question.retakes ?? 0, 10) || 0);
            state.originalRetakes = state.remainingRetakes;
            els.retakeCount.textContent = String(state.remainingRetakes);
            setQuestionStatus('Ready', 'ready');
            setButtonLabel('Start');
            setQuestionButtonEnabled(true);
            updateRetakeUI();
        } catch (error) {
            console.error('Interview question load failed:', error);
            setQuestionStatus('Unable to load', 'ready');
            showSystemAlert({
                title: 'Unable to load question',
                message: error?.message || 'Please wait a moment and try again.',
                type: 'error'
            });
        } finally {
            state.isLoadingQuestion = false;
            if (!state.currentQuestion) setQuestionButtonEnabled(false);
        }
    }

    function startThinking(seconds) {
        stopAllTimers();
        const total = Math.max(0, Math.floor(seconds || 0));

        if (total <= 0) {
            els.thinkingOverlay.style.display = 'none';
            startAnswer();
            return;
        }

        let remaining = total;
        els.thinkingOverlay.style.display = 'flex';
        els.thinkingTimer.textContent = String(remaining);
        setThinkingProgress(remaining, total);
        setQuestionStatus('Thinking time', 'ready');

        state.thinkingTimerId = setInterval(() => {
            remaining -= 1;
            els.thinkingTimer.textContent = String(Math.max(0, remaining));
            setThinkingProgress(Math.max(0, remaining), total);

            if (remaining <= 0) {
                clearInterval(state.thinkingTimerId);
                state.thinkingTimerId = null;
                els.thinkingOverlay.style.display = 'none';
                startAnswer();
            }
        }, 1000);
    }

    function startTextTimer() {
        if (state.answerSeconds <= 0) {
            updateRemainingTime(0);
            submit();
            return;
        }

        state.remainingSeconds = state.answerSeconds;
        updateRemainingTime(state.remainingSeconds);
        state.answerTimerId = setInterval(() => {
            state.remainingSeconds -= 1;
            updateRemainingTime(state.remainingSeconds);
            if (state.remainingSeconds <= 0) {
                clearInterval(state.answerTimerId);
                state.answerTimerId = null;
                submit({ auto: true });
            }
        }, 1000);
    }

    function startAnswer() {
        if (!state.currentQuestion || state.isSubmitting) return;

        state.hasStarted = true;
        state.isAutoStop = false;
        setQuestionStatus(MODE.TEXT ? 'Answering' : 'Recording', 'live');
        setBeforeUnloadGuard(true);

        if (state.currentQuestionNumber === state.totalQuestions) setButtonLabel('Submit');
        else setButtonLabel('Save & Next');

        if (MODE.TEXT) {
            els.textarea.disabled = false;
            els.textarea.focus({ preventScroll: true });
            startTextTimer();
            updateActionLayout();
            return;
        }

        startRecording().catch(error => {
            console.error('Recording start failed:', error);
            setQuestionStatus('Recording unavailable', 'ready');
            showSystemAlert({
                title: 'Unable to start recording',
                message: error?.message || 'Please check your camera and microphone permissions, then try again.',
                type: 'error'
            });
            setQuestionButtonEnabled(true);
        });
    }

    async function startRecording() {
        if (!navigator.mediaDevices?.getUserMedia) throw new Error('Your browser does not support media recording.');
        if (!window.MediaRecorder) throw new Error('Media recording is not supported by this browser.');

        cleanupMedia();
        state.chunks = [];
        state.recordedUrl = null;
        state.currentMimeType = getSupportedMimeType();

        const mediaStream = await navigator.mediaDevices.getUserMedia(mediaConstraints());
        state.stream = mediaStream;
        state.mediaPermissionGranted = true;

        if (MODE.VIDEO) {
            els.preview.classList.remove('d-none');
            els.recorded.classList.add('d-none');
            els.preview.srcObject = mediaStream;
            els.preview.muted = true;
            els.preview.playsInline = true;
            els.preview.onloadedmetadata = updateVideoOrientation;
            await els.preview.play().catch(() => {});
            updateVideoOrientation();
        } else {
            els.previewAudio.srcObject = mediaStream;
            els.previewAudio.muted = true;
            await els.previewAudio.play().catch(() => {});
            await attachAudioMonitor(mediaStream);
            els.audioState.innerHTML = '<span class="recording-dot" aria-hidden="true"></span><span>Recording your answer…</span>';
        }

        const options = {};
        if (state.currentMimeType) options.mimeType = state.currentMimeType;
        if (MODE.VIDEO) {
            options.videoBitsPerSecond = 2500000;
            options.audioBitsPerSecond = 128000;
        } else {
            options.audioBitsPerSecond = 128000;
        }

        let recorder;
        try {
            recorder = new MediaRecorder(mediaStream, options);
        } catch (error) {
            // Some browsers reject optional bitrate/mime combinations. Fall back
            // to the browser's default codec rather than leaving the camera open.
            recorder = new MediaRecorder(mediaStream);
            state.currentMimeType = recorder.mimeType || state.currentMimeType;
        }

        state.recorder = recorder;
        state.currentMimeType = recorder.mimeType || state.currentMimeType;
        state.isRecording = true;
        state.isAutoStop = false;

        recorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) state.chunks.push(event.data);
        };

        recorder.onerror = (event) => {
            console.error('MediaRecorder error:', event?.error || event);
            showSystemAlert({
                title: 'Recording error',
                message: 'The browser reported a recording error. Please try again.',
                type: 'error'
            });
        };

        recorder.onstop = handleRecordingStop;
        recorder.start(1000);

        els.recordStopBtn.classList.remove('d-none');
        els.questionBtn.classList.add('d-none');
        els.recordResetBtn.classList.add('d-none');
        els.recordingPill.classList.add('is-visible');
        updateActionLayout();
        startRecordTimer();
    }

    function startRecordTimer() {
        if (state.answerSeconds <= 0) {
            stopRecording(true);
            return;
        }

        stopAllTimers();
        state.remainingSeconds = state.answerSeconds;
        updateRemainingTime(state.remainingSeconds);

        state.answerTimerId = setInterval(() => {
            state.remainingSeconds -= 1;
            updateRemainingTime(state.remainingSeconds);

            if (state.remainingSeconds <= 0) {
                clearInterval(state.answerTimerId);
                state.answerTimerId = null;
                state.isAutoStop = true;
                stopRecording(true);
            }
        }, 1000);
    }

    function stopRecording(autoStop = false) {
        if (!state.recorder) return;
        if (state.recorder.state !== 'recording') return;
        state.isAutoStop = Boolean(autoStop);
        if (state.answerTimerId) clearInterval(state.answerTimerId);
        state.answerTimerId = null;
        try {
            state.recorder.stop();
        } catch (error) {
            console.error('Recorder stop failed:', error);
            handleRecordingStop();
        }
    }

    function handleRecordingStop() {
        state.isRecording = false;
        if (state.answerTimerId) clearInterval(state.answerTimerId);
        state.answerTimerId = null;
        els.recordStopBtn.classList.add('d-none');
        els.recordingPill.classList.remove('is-visible');

        if (!state.chunks.length) {
            cleanupMedia();
            showSystemAlert({
                title: 'No recording captured',
                message: 'No usable recording data was produced. Please check your device and try again.',
                type: 'warning'
            });
            els.questionBtn.classList.remove('d-none');
            setQuestionButtonEnabled(true);
            updateActionLayout();
            return;
        }

        const type = state.currentMimeType || (MODE.VIDEO ? 'video/webm' : 'audio/webm');
        const blob = new Blob(state.chunks, { type });
        if (!blob.size) {
            showSystemAlert({ title: 'Empty recording', message: 'The recording file was empty. Please record your answer again.', type: 'warning' });
            els.questionBtn.classList.remove('d-none');
            setQuestionButtonEnabled(true);
            return;
        }

        cleanupMedia({ keepRecorded: true });
        state.recordedUrl = URL.createObjectURL(blob);
        state.chunks = [blob];
        state.currentMimeType = blob.type || type;

        if (MODE.VIDEO) {
            els.preview.classList.add('d-none');
            els.preview.srcObject = null;
            els.recorded.src = state.recordedUrl;
            els.recorded.classList.remove('d-none');
            els.recorded.onloadedmetadata = () => {
                const portrait = els.recorded.videoHeight > els.recorded.videoWidth;
                els.videoInterview.classList.toggle('is-portrait', portrait);
                els.videoInterview.classList.toggle('is-landscape', !portrait);
                els.cameraModePill.textContent = portrait ? 'Portrait recording' : 'Landscape recording';
            };
        } else {
            els.previewAudio.srcObject = null;
            stopWaveform();
            els.recordedAudio.src = state.recordedUrl;
            els.recordedAudio.classList.remove('d-none');
            els.audioState.innerHTML = '<span class="mdi mdi-check-circle-outline" aria-hidden="true"></span><span>Recording ready to review</span>';
        }

        setQuestionStatus('Recording ready', 'ready');
        els.questionBtn.classList.remove('d-none');
        setButtonLabel(state.currentQuestionNumber === state.totalQuestions ? 'Submit' : 'Save & Next');
        setQuestionButtonEnabled(true);
        updateRetakeUI();

        if (state.isAutoStop) {
            // Preserve the original behavior: an answer that naturally reaches
            // its server-defined time limit is submitted automatically.
            submit({ auto: true });
        }
    }

    function handleTextInput() {
        const max = Number(els.textarea.maxLength || 1000);
        if (els.textarea.value.length > max) els.textarea.value = els.textarea.value.slice(0, max);
        els.charCount.textContent = `${els.textarea.value.length}/${max}`;
    }

    function getAnswerBlob() {
        if (!state.chunks.length) return null;
        const type = state.currentMimeType || (MODE.VIDEO ? 'video/webm' : 'audio/webm');
        return state.chunks[0] instanceof Blob ? state.chunks[0] : new Blob(state.chunks, { type });
    }

    function submit({ auto = false } = {}) {
        if (state.isSubmitting) return;
        if (!state.currentQuestion?.question_id) {
            showSystemAlert({ title: 'Question not ready', message: 'Please wait for the current question to finish loading.', type: 'error' });
            return;
        }
        if (!navigator.onLine) {
            showSystemAlert({ title: 'Connection lost', message: 'Reconnect to the internet before submitting this answer.', type: 'warning' });
            return;
        }

        if (MODE.TEXT) {
            if (!els.textarea.value.trim()) {
                showSystemAlert({ title: 'Answer required', message: 'Please enter an answer before continuing.', type: 'warning' });
                return;
            }
        } else {
            const blob = getAnswerBlob();
            if (!blob || !blob.size) {
                showSystemAlert({ title: 'Recording required', message: 'Please record your answer before continuing.', type: 'warning' });
                return;
            }
        }

        state.isSubmitting = true;
        stopAllTimers();
        if (state.recorder?.state === 'recording') {
            stopRecording(false);
            // Do not submit until onstop has produced the final blob.
            state.isSubmitting = false;
            return;
        }

        setBeforeUnloadGuard(false);
        setQuestionButtonEnabled(false);
        els.recordResetBtn.classList.add('d-none');
        els.resetInfo.classList.add('d-none');
        setModal(els.uploadModal, true);
        els.uploadProgress.style.width = '0%';
        els.uploadProgress.setAttribute('aria-valuenow', '0');
        els.uploadMessage.textContent = auto ? 'Your time ended. Saving the captured answer…' : 'Please keep this page open until the upload finishes.';

        const formData = new FormData();
        formData.append('session_token', SESSION_TOKEN || '');
        formData.append('question_id', state.currentQuestion.question_id);
        formData.append('answer_type', MODE.TEXT ? 'assessment' : (MODE.VIDEO ? 'video' : 'audio'));
        formData.append('time_taken', String(Math.max(0, state.answerSeconds - state.remainingSeconds)));
        formData.append('retake_count', String(Math.max(0, state.originalRetakes - state.remainingRetakes)));

        if (MODE.TEXT) {
            formData.append('answer_text', els.textarea.value.trim());
        } else {
            const blob = getAnswerBlob();
            const extension = MODE.VIDEO ? 'webm' : 'webm';
            formData.append('answer_file', blob, `interview-answer-${Date.now()}.${extension}`);
        }

        const xhr = new XMLHttpRequest();
        state.xhr = xhr;
        xhr.open('POST', '/interview/answer/save', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', @json(csrf_token()));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.timeout = 120000;

        xhr.upload.onprogress = (event) => {
            if (!event.lengthComputable) return;
            const percent = Math.max(0, Math.min(100, Math.round((event.loaded / event.total) * 100)));
            els.uploadProgress.style.width = `${percent}%`;
            els.uploadProgress.setAttribute('aria-valuenow', String(percent));
        };

        xhr.onload = async () => {
            state.xhr = null;
            if (xhr.status >= 200 && xhr.status < 300) {
                els.uploadProgress.style.width = '100%';
                els.uploadProgress.setAttribute('aria-valuenow', '100');
                cleanupMedia();
                setModal(els.uploadModal, false);
                state.isSubmitting = false;
                state.currentQuestion = null;
                await loadNextQuestion();
                return;
            }

            state.isSubmitting = false;
            setModal(els.uploadModal, false);
            setQuestionButtonEnabled(true);
            showSystemAlert({
                title: 'Unable to save answer',
                message: parseServerError(xhr.responseText) || `The server returned an error (${xhr.status}). Please try again.`,
                type: 'error'
            });
        };

        xhr.onerror = () => {
            state.xhr = null;
            state.isSubmitting = false;
            setModal(els.uploadModal, false);
            setQuestionButtonEnabled(true);
            showSystemAlert({ title: 'Upload failed', message: 'The answer could not be uploaded. Check your connection and try again.', type: 'error' });
        };

        xhr.ontimeout = () => {
            state.xhr = null;
            state.isSubmitting = false;
            setModal(els.uploadModal, false);
            setQuestionButtonEnabled(true);
            showSystemAlert({ title: 'Upload timed out', message: 'The network is taking too long. Please reconnect and try again.', type: 'error' });
        };

        xhr.onabort = () => {
            state.xhr = null;
            state.isSubmitting = false;
            setModal(els.uploadModal, false);
            setQuestionButtonEnabled(true);
        };

        xhr.send(formData);
    }

    function parseServerError(raw) {
        if (!raw) return '';
        try {
            const data = JSON.parse(raw);
            return data.message || data.error || '';
        } catch (_) {
            return '';
        }
    }

    function retakeAnswer() {
        if (state.remainingRetakes <= 0 || state.isSubmitting) return;
        stopAllTimers();
        cleanupMedia();
        state.remainingRetakes -= 1;
        state.hasStarted = false;
        state.isAutoStop = false;
        state.recordedUrl = null;
        state.chunks = [];
        updateRetakeUI();
        updateRemainingTime(state.answerSeconds);
        els.questionBtn.classList.remove('d-none');
        els.recordStopBtn.classList.add('d-none');
        setButtonLabel('Start');
        setQuestionButtonEnabled(true);
        setQuestionStatus('Ready for retake', 'ready');
    }

    async function finalizeInterview(url) {
        stopAllTimers();
        setBeforeUnloadGuard(false);
        cleanupMedia();
        state.isSubmitting = true;
        if (!url) {
            showSystemAlert({ title: 'Interview completed', message: 'Your interview has been submitted successfully.', type: 'success' });
            return;
        }
        // Use the server-provided redirect only. Do not construct a completion
        // URL from user-controlled values in the browser.
        window.location.assign(url);
    }

    async function toggleVideoFullscreen() {
        const target = els.videoStage;
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
                return;
            }
            if (target.requestFullscreen) {
                await target.requestFullscreen();
            } else if (els.preview.webkitEnterFullscreen) {
                els.preview.webkitEnterFullscreen();
            }
        } catch (error) {
            console.warn('Fullscreen unavailable:', error);
        }
    }

    function logTabSwitch() {
        // This is an audit signal, not proof of cheating. The server should
        // decide how such events affect an interview.
        try {
            fetch('/interview/tab-switch', {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    session_token: SESSION_TOKEN,
                    event: 'visibility_hidden',
                    count: state.tabSwitchCount,
                    timestamp: new Date().toISOString()
                })
            }).catch(() => {});
        } catch (_) {}
    }

    function handleVisibilityChange() {
        if (!state.hasStarted || state.isSubmitting) return;
        if (document.hidden) {
            state.tabSwitchCount += 1;
            logTabSwitch();
            showSystemAlert({
                title: 'Interview rule reminder',
                message: 'Please stay on the interview page and avoid switching tabs or applications. Repeated events may be reviewed by the interviewer.',
                type: 'warning'
            });
        }
    }

    function handleOnlineState(isOnline) {
        els.networkBanner.classList.toggle('is-visible', !isOnline);
        if (!isOnline) {
            showSystemAlert({ title: 'Connection lost', message: 'Your internet connection was interrupted. Do not close this page. Reconnect before continuing.', type: 'warning' });
        }
    }

    function bindEvents() {
        els.questionBtn.addEventListener('click', () => {
            if (state.isSubmitting || state.isLoadingQuestion) return;

            if (!state.hasStarted) {
                state.hasStarted = true;
                setButtonLabel(state.currentQuestionNumber === state.totalQuestions ? 'Submit' : 'Save & Next');
                setQuestionButtonEnabled(false);
                startThinking(state.thinkingSeconds);
                return;
            }

            if (MODE.TEXT || state.recordedUrl) submit();
        });

        els.recordStopBtn.addEventListener('click', () => stopRecording(false));
        els.recordResetBtn.addEventListener('click', retakeAnswer);
        els.textarea.addEventListener('input', handleTextInput);
        els.retrySystemCheckBtn.addEventListener('click', async () => {
            const ok = await startSystemCheck();
            if (ok) await beginInterviewAfterSystemCheck();
        });
        els.closeAlertBtn.addEventListener('click', closeSystemAlert);
        els.videoFullscreenBtn.addEventListener('click', toggleVideoFullscreen);

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('online', () => handleOnlineState(true));
        window.addEventListener('offline', () => handleOnlineState(false));

        window.addEventListener('beforeunload', () => {
            if (state.stream) state.stream.getTracks().forEach(track => track.stop());
            if (state.xhr && state.xhr.readyState !== XMLHttpRequest.DONE) {
                try { state.xhr.abort(); } catch (_) {}
            }
        });

        window.addEventListener('pagehide', () => {
            stopAllTimers();
            cleanupMedia();
        });

        document.addEventListener('fullscreenchange', () => {
            const icon = els.videoFullscreenBtn.querySelector('.mdi');
            if (icon) {
                icon.className = document.fullscreenElement ? 'mdi mdi-fullscreen-exit' : 'mdi mdi-fullscreen';
            }
        });
    }

    async function init() {
        showModeSpecificInitialState();
        els.page.style.display = 'none';
        setModal(els.modal, true);
        setQuestionButtonEnabled(false);
        updateRemainingTime(0);
        setQuestionStatus('System check', 'ready');

        // Never persist a "passed" permission flag across refreshes. Browser
        // permissions are checked again for every interview page load.
        bindEvents();

        const ok = await startSystemCheck();
        if (ok) {
            await beginInterviewAfterSystemCheck();
        }
    }

    // Keep the original global helpers available if another page script calls them.
    window.startInterview = () => els.questionBtn.click();
    window.closeSystemAlert = closeSystemAlert;
    window.handleNextBtn = () => {};

    document.addEventListener('DOMContentLoaded', init, { once: true });
})();
</script>
<!-- https://chatgpt.com/share/6a949a48-b82c-83e8-a2bc-ba1f99f7a6d0 -->
@endsection
