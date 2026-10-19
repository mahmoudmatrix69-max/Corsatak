<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7faff">

    <title>Page Not Have Permission</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #1677ff;
            --blue-dark: #075edc;
            --blue-light: #5aa5ff;
            --text: #182338;
            --muted: #718096;
            --white: #ffffff;
            --background: #f7faff;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: 'Cairo', sans-serif;
            color: var(--text);
            background: var(--background);
            overflow: hidden;
            cursor: auto;
        }

        .page {
            position: relative;
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;
            isolation: isolate;
        }

        .background {
            position: absolute;
            inset: 0;
            z-index: -10;
            overflow: hidden;

            background:
                radial-gradient(circle at 50% 35%,
                    rgba(45, 135, 255, .11),
                    transparent 32%),
                linear-gradient(180deg,
                    #ffffff 0%,
                    #f8fbff 55%,
                    #f2f7ff 100%);
        }

        .glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(2px);
        }

        .glow.one {
            width: 450px;
            height: 450px;

            top: -180px;
            right: -100px;

            background: rgba(42, 126, 255, .08);

            animation: floatGlow 9s ease-in-out infinite;
        }

        .glow.two {
            width: 380px;
            height: 380px;

            bottom: -180px;
            left: -100px;

            background: rgba(0, 174, 255, .07);

            animation: floatGlow 11s ease-in-out infinite reverse;
        }

        .glow.three {
            width: 180px;
            height: 180px;

            top: 35%;
            left: 7%;

            background: rgba(83, 156, 255, .07);

            animation: pulseGlow 5s ease-in-out infinite;
        }

        .decor {
            position: absolute;
            pointer-events: none;
        }

        .circle {
            border-radius: 50%;
            border: 2px solid rgba(22, 119, 255, .16);
        }

        .circle.a {
            width: 23px;
            height: 23px;

            top: 14%;
            left: 20%;

            animation: float 5s ease-in-out infinite;
        }

        .circle.b {
            width: 13px;
            height: 13px;

            top: 21%;
            right: 13%;

            border: 0;
            background: rgba(22, 119, 255, .17);

            animation: float 4s ease-in-out infinite reverse;
        }

        .circle.c {
            width: 15px;
            height: 15px;

            bottom: 24%;
            left: 17%;

            border: 0;
            background: rgba(22, 119, 255, .12);

            animation: float 6s ease-in-out infinite;
        }

        .cross {
            position: absolute;
            width: 18px;
            height: 18px;
            opacity: .4;
        }

        .cross::before,
        .cross::after {
            content: "";

            position: absolute;

            width: 3px;
            height: 18px;

            top: 0;
            left: 7px;

            border-radius: 10px;

            background: var(--blue);
        }

        .cross::after {
            transform: rotate(90deg);
        }

        .cross.one {
            top: 16%;
            right: 28%;

            animation: spin 8s linear infinite;
        }

        .cross.two {
            bottom: 25%;
            right: 17%;

            animation: spin 10s linear infinite reverse;
        }

        .cross.three {
            bottom: 18%;
            left: 27%;

            transform: scale(.7);

            animation: spin 7s linear infinite;
        }

        .dots {
            position: absolute;

            width: 65px;
            height: 65px;

            background-image:
                radial-gradient(circle,
                    rgba(22, 119, 255, .24) 2px,
                    transparent 2px);

            background-size: 15px 15px;

            opacity: .55;
        }

        .dots.one {
            top: 23%;
            left: 8%;

            animation: moveDots 6s ease-in-out infinite;
        }

        .dots.two {
            bottom: 20%;
            right: 8%;

            animation: moveDots 7s ease-in-out infinite reverse;
        }

        .content {
            position: relative;
            z-index: 5;

            width: min(1100px, 100%);

            min-height: 100vh;
            min-height: 100dvh;

            padding: 25px 25px 20px;

            display: flex;
            flex-direction: column;

            justify-content: center;
            align-items: center;

            text-align: center;
        }

        .error-visual {
            position: relative;

            width: min(850px, 90vw);
            height: 360px;

            display: flex;
            justify-content: center;
            align-items: center;

            margin-bottom: 2px;

            animation:
                visualEntrance 1s cubic-bezier(.2, .8, .2, 1) both;
        }

        .number {
            position: absolute;

            top: 2px;
            left: 50%;

            transform: translateX(-50%);

            z-index: -2;

            display: flex;
            align-items: center;
            justify-content: center;

            direction: ltr;

            white-space: nowrap;

            font-family: Arial, sans-serif;

            font-size:
                clamp(205px,
                    28vw,
                    345px);

            font-weight: 900;

            line-height: .88;

            letter-spacing: -12px;

            color: #dfe8f4;
            text-shadow: 0 2px 0 rgba(255, 255, 255, .7);

            user-select: none;
        }

        .number .four {
            display: block;
            margin-right: 120px;
        }

        .number .zero {
            display: block;
        }

        .number .three {
            display: block;
            margin-left: 120px;
        }

        .robot {
            position: relative;

            width: 235px;
            height: 235px;

            border-radius: 65px;

            background:
                linear-gradient(145deg,
                    #ffffff 0%,
                    #f7f9fc 45%,
                    #e9eff7 100%);

            border: 4px solid #dce5f0;

            box-shadow:
                0 30px 70px rgba(25, 80, 150, .16),
                0 8px 20px rgba(20, 70, 130, .07),
                inset 0 2px 5px rgba(255, 255, 255, .95);

            animation:
                robotFloat 4s ease-in-out infinite;
        }

        .robot::before {
            content: "";

            position: absolute;

            inset: 12px;

            border-radius: 53px;

            border: 1px solid rgba(22, 119, 255, .08);

            pointer-events: none;
        }

        .robot-dot {
            position: absolute;

            width: 12px;
            height: 12px;

            border-radius: 50%;

            background: #b9c6d7;

            box-shadow:
                inset 0 2px 3px rgba(0, 0, 0, .08);
        }

        .robot-dot:nth-child(1) {
            top: 26px;
            left: 28px;
        }

        .robot-dot:nth-child(2) {
            top: 26px;
            right: 28px;
        }

        .robot-dot:nth-child(3) {
            bottom: 26px;
            left: 28px;
        }

        .robot-dot:nth-child(4) {
            bottom: 26px;
            right: 28px;
        }

        .eye {
            position: absolute;

            width: 145px;
            height: 145px;

            top: 50%;
            left: 50%;

            transform:
                translate(-50%, -50%);

            border-radius: 50%;

            overflow: hidden;

            background: #ffffff;

            border: 3px solid #d8e1ec;

            box-shadow:
                inset 0 0 20px rgba(0, 0, 0, .075),
                inset 0 -10px 18px rgba(0, 0, 0, .025),
                0 8px 25px rgba(0, 0, 0, .08);
        }

        .eyeball {
            position: absolute;

            inset: 0;

            border-radius: 50%;

            overflow: hidden;

            background:
                radial-gradient(ellipse at 48% 43%,
                    #ffffff 0%,
                    #ffffff 38%,
                    #f9fafb 57%,
                    #eef1f4 77%,
                    #dfe5eb 100%);
        }

        .eyeball::before {
            content: "";

            position: absolute;

            inset: 0;

            border-radius: 50%;

            background:
                radial-gradient(ellipse at 32% 24%,
                    rgba(255, 255, 255, .95),
                    transparent 34%);

            pointer-events: none;
        }

        .eyeball::after {
            content: "";

            position: absolute;

            inset: 0;

            border-radius: 50%;

            box-shadow:
                inset 0 0 25px rgba(20, 35, 50, .075);

            pointer-events: none;
        }

        .iris {
            position: absolute;
            transition: none;
            will-change: transform;

            width: 76px;
            height: 76px;

            left: 50%;
            top: 54%;

            transform:
                translate3d(-50%,
                    -50%,
                    0);

            border-radius: 50%;

            overflow: hidden;

            background:
                radial-gradient(circle at 50% 50%,
                    #020609 0%,
                    #020609 15%,
                    #061421 18%,
                    #0a2941 27%,
                    #075582 40%,
                    #008cff 53%,
                    #006ac7 65%,
                    #034274 78%,
                    #021e3d 90%,
                    #010b16 100%);

            box-shadow:
                0 0 0 4px rgba(0, 110, 205, .12),
                0 0 18px rgba(0, 119, 255, .22);

            will-change: transform;
        }

        .iris::before {
            content: "";

            position: absolute;

            inset: 4px;

            border-radius: 50%;

            background:
                repeating-conic-gradient(from 0deg,

                    rgba(120, 210, 255, .38) 0deg,
                    rgba(5, 50, 85, .04) 4deg,

                    rgba(90, 190, 255, .26) 8deg,
                    rgba(0, 25, 50, .03) 13deg);

            opacity: .7;

            mix-blend-mode: screen;
        }

        .iris::after {
            content: "";

            position: absolute;

            inset: 12px;

            border-radius: 50%;

            border:
                2px solid rgba(82, 190, 255, .30);

            box-shadow:
                inset 0 0 8px rgba(0, 0, 0, .5);
        }

        .pupil {
            position: absolute;

            width: 34px;
            height: 42px;

            left: 50%;
            top: 50%;

            transform:
                translate(-50%, -50%);

            border-radius: 50%;

            background:
                radial-gradient(ellipse at 40% 30%,
                    #111a22 0%,
                    #020608 48%,
                    #000000 100%);

            box-shadow:
                inset 0 0 8px rgba(255, 255, 255, .035),
                0 0 7px rgba(0, 0, 0, .35);

            z-index: 4;
        }

        .eye-glint {
            position: absolute;

            width: 17px;
            height: 17px;

            border-radius: 50%;

            top: 10px;
            right: 12px;

            background:
                radial-gradient(circle,
                    #ffffff 0%,
                    rgba(255, 255, 255, .95) 45%,
                    rgba(255, 255, 255, 0) 100%);

            opacity: .95;

            z-index: 8;
        }

        .eye-glint::after {
            content: "";

            position: absolute;

            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #ffffff;

            left: -8px;
            bottom: -25px;

            opacity: .4;
        }

        .angry-lid {
            position: absolute;

            width: 180px;
            height: 108px;

            left: 50%;
            top: -47px;

            transform:
                translateX(-50%) rotate(-10deg);

            background:
                linear-gradient(180deg,
                    #e9eef5 0%,
                    #ffffff 55%,
                    rgba(255, 255, 255, 0) 62%);

            border-radius: 50%;

            z-index: 5;

            box-shadow:
                0 5px 8px rgba(0, 0, 0, .06);
        }

        .angry-brow {
            position: absolute;

            width: 95px;
            height: 15px;

            top: 7px;
            left: 50%;

            transform:
                translateX(-50%) rotate(-9deg);

            border-radius: 100px;

            background: #263346;

            z-index: 8;

            box-shadow:
                0 3px 5px rgba(0, 0, 0, .12);
        }

        .eye::after {
            content: "";

            position: absolute;

            inset: 7px;

            border-radius: 50%;

            border:
                1px solid rgba(255, 255, 255, .7);

            pointer-events: none;

            z-index: 10;
        }

        .beacon {
            position: absolute;

            width: 54px;
            height: 48px;

            top: -48px;
            left: 50%;

            transform:
                translateX(-50%);

            border-radius:
                25px 25px 8px 8px;

            background:
                linear-gradient(180deg,
                    #2d8dff,
                    #0964e8);

            border: 4px solid #dce5f0;

            box-shadow:
                0 -5px 25px rgba(22, 119, 255, .3);

            animation:
                beacon 1.8s ease-in-out infinite;
        }

        .beacon::after {
            content: "";

            position: absolute;

            width: 9px;
            height: 18px;

            border-radius: 10px;

            background: #ffffff;

            opacity: .35;

            top: 7px;
            left: 13px;
        }

        .signal {
            position: absolute;

            top: -80px;
            left: 50%;

            transform:
                translateX(-50%) scale(.5);

            width: 90px;
            height: 90px;

            border-radius: 50%;

            border:
                2px solid rgba(22, 119, 255, .25);

            animation:
                signal 2s ease-out infinite;
        }

        .signal.two {
            animation-delay: 1s;
        }

        .ring {
            position: absolute;

            width: 315px;
            height: 315px;

            border-radius: 50%;

            border:
                1px solid rgba(22, 119, 255, .09);

            animation:
                rotateRing 15s linear infinite;
        }

        .ring::before,
        .ring::after {
            content: "";

            position: absolute;

            width: 8px;
            height: 8px;

            border-radius: 50%;

            background: var(--blue);

            box-shadow:
                0 0 15px rgba(22, 119, 255, .6);
        }

        .ring::before {
            top: 20px;
            left: 50%;
        }

        .ring::after {
            bottom: 35px;
            right: 18px;
        }

        .cursor-line {
            position: absolute;

            width: 180px;
            height: 100px;

            right: 45px;
            bottom: 28px;

            border-bottom:
                3px dashed rgba(22, 119, 255, .35);

            border-radius:
                0 0 100px 0;

            transform: rotate(10deg);
        }

        .cursor {
            position: absolute;

            right: 15px;
            bottom: 0;

            width: 0;
            height: 0;

            border-top:
                18px solid transparent;

            border-bottom:
                18px solid transparent;

            border-left:
                28px solid #172033;

            transform: rotate(-25deg);

            filter:
                drop-shadow(0 4px 4px rgba(0, 0, 0, .18));

            animation:
                cursorMove 3s ease-in-out infinite;
        }

        .cursor::after {
            content: "";

            position: absolute;

            width: 0;
            height: 0;

            border-top:
                13px solid transparent;

            border-bottom:
                13px solid transparent;

            border-left:
                21px solid #ffffff;

            left: -24px;
            top: -13px;
        }

        .title {
            font-size:
                clamp(30px,
                    4vw,
                    50px);

            line-height: 1.3;

            font-weight: 900;

            color: #182338;

            animation:
                fadeUp .8s .25s both;
        }

        .title::after {
            content: "";

            display: block;

            width: 0;
            height: 5px;

            margin:
                12px auto 0;

            border-radius: 20px;

            background:
                linear-gradient(90deg,
                    #0d63e8,
                    #51a1ff);

            animation:
                lineGrow .8s .6s forwards;
        }

        .description {
            max-width: 650px;

            margin:
                16px auto 0;

            color: var(--muted);

            font-size:
                clamp(14px,
                    1.7vw,
                    17px);

            line-height: 2;

            animation:
                fadeUp .8s .4s both;
        }

        .actions {
            margin-top: 22px;

            animation:
                fadeUp .8s .55s both;
        }

        .home-btn {
            position: relative;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 10px;

            min-width: 245px;

            padding:
                14px 25px;

            border-radius: 14px;

            color: #ffffff;

            background:
                linear-gradient(135deg,
                    #2082ff,
                    #075edc);

            text-decoration: none;

            font-size: 16px;
            font-weight: 800;

            box-shadow:
                0 12px 25px rgba(22, 119, 255, .25),

                0 4px 0 #064eb9;

            overflow: hidden;

            transition:
                transform .25s ease,
                box-shadow .25s ease;
        }

        .home-btn::before {
            content: "";

            position: absolute;

            width: 50px;
            height: 150%;

            left: -90px;
            top: -25%;

            background:
                rgba(255, 255, 255, .25);

            transform: rotate(25deg);

            transition:
                left .6s ease;
        }

        .home-btn:hover {
            transform: translateY(-3px);

            box-shadow:
                0 18px 32px rgba(22, 119, 255, .28),

                0 4px 0 #064eb9;
        }

        .home-btn:hover::before {
            left: 110%;
        }

        .home-btn:active {
            transform: translateY(1px);

            box-shadow:
                0 7px 16px rgba(22, 119, 255, .2);
        }

        .home-icon {
            width: 21px;
            height: 21px;

            fill: currentColor;
        }

        .footer {
            margin-top: 24px;

            color: #9aa7b8;

            font-size: 12px;

            animation:
                fadeUp .8s .7s both;
        }

        .wave {
            position: absolute;

            z-index: -2;

            left: -5%;

            width: 110%;
            height: 170px;

            bottom: -90px;

            border-radius:
                50% 50% 0 0;

            background:
                rgba(22, 119, 255, .07);

            animation:
                waveMove 8s ease-in-out infinite;
        }

        .wave.two {
            height: 125px;
            bottom: -65px;

            background:
                rgba(22, 119, 255, .045);

            animation-duration: 11s;

            animation-direction: reverse;
        }

        .wave.three {
            height: 80px;
            bottom: -45px;

            background:
                rgba(22, 119, 255, .025);

            animation-duration: 14s;
        }

        @keyframes visualEntrance {

            from {
                opacity: 0;

                transform:
                    translateY(35px) scale(.94);
            }

            to {
                opacity: 1;

                transform: none;
            }
        }

        @keyframes fadeUp {

            from {
                opacity: 0;

                transform:
                    translateY(18px);
            }

            to {
                opacity: 1;

                transform: none;
            }
        }

        @keyframes robotFloat {

            0%,
            100% {
                transform:
                    translateY(0) rotate(0);
            }

            50% {
                transform:
                    translateY(-9px) rotate(.7deg);
            }
        }

        @keyframes beacon {

            0%,
            100% {
                opacity: .85;

                transform:
                    translateX(-50%) scaleY(1);
            }

            50% {
                opacity: 1;

                transform:
                    translateX(-50%) scaleY(1.08);
            }
        }

        @keyframes signal {

            0% {
                opacity: .7;

                transform:
                    translateX(-50%) scale(.5);
            }

            100% {
                opacity: 0;

                transform:
                    translateX(-50%) scale(1.3);
            }
        }

        @keyframes rotateRing {

            from {
                transform: rotate(0);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform:
                    translateY(0);
            }

            50% {
                transform:
                    translateY(-15px);
            }
        }

        @keyframes floatGlow {

            0%,
            100% {
                transform:
                    translate(0, 0) scale(1);
            }

            50% {
                transform:
                    translate(-20px, 20px) scale(1.05);
            }
        }

        @keyframes pulseGlow {

            0%,
            100% {
                transform: scale(1);
                opacity: .5;
            }

            50% {
                transform: scale(1.25);
                opacity: 1;
            }
        }

        @keyframes moveDots {

            0%,
            100% {
                transform:
                    translate(0, 0);
            }

            50% {
                transform:
                    translate(10px, -12px);
            }
        }

        @keyframes spin {

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes lineGrow {

            from {
                width: 0;
                opacity: 0;
            }

            to {
                width: 65px;
                opacity: 1;
            }
        }

        @keyframes waveMove {

            0%,
            100% {
                transform:
                    translateX(-2%) rotate(1deg);
            }

            50% {
                transform:
                    translateX(2%) rotate(-1deg);
            }
        }

        @media (max-width:1100px) {
            .content {
                padding-left: 20px;
                padding-right: 20px;
            }

            .error-visual {
                width: min(780px, 92vw);
            }

            .number {
                font-size: clamp(180px, 29vw, 300px);
            }

            .number .four {
                margin-right: 105px;
            }

            .number .three {
                margin-left: 105px;
            }
        }

        @media (max-width:800px) {
            body {
                overflow: auto;
                cursor: auto;
            }

            .page {
                min-height: 100svh;
                min-height: 100dvh;
                overflow: hidden;
            }

            .content {
                min-height: 100svh;
                min-height: 100dvh;
                padding: 24px 18px 30px;
            }

            .error-visual {
                width: 100%;
                height: 315px;
                margin-bottom: 0;
            }

            .number {
                top: 0;
                font-size: clamp(165px, 30vw, 235px);
                letter-spacing: -12px;
            }

            .number .four {
                margin-right: 80px;
            }

            .number .three {
                margin-left: 80px;
            }

            .robot {
                width: 195px;
                height: 195px;
            }

            .eye {
                width: 122px;
                height: 122px;
            }

            .iris {
                width: 65px;
                height: 65px;
            }

            .pupil {
                width: 30px;
                height: 34px;
            }

            .angry-lid {
                width: 150px;
                height: 90px;
                top: -38px;
            }

            .angry-brow {
                width: 78px;
                height: 12px;
            }

            .beacon {
                width: 46px;
                height: 41px;
                top: -42px;
            }

            .signal {
                top: -68px;
                width: 75px;
                height: 75px;
            }

            .ring {
                width: 270px;
                height: 270px;
            }

            .title {
                font-size: clamp(27px, 5vw, 38px);
            }

            .description {
                max-width: 580px;
                font-size: clamp(13px, 2vw, 16px);
            }

            .actions {
                margin-top: 19px;
            }

            .footer {
                margin-top: 20px;
            }

            .cursor-line,
            .cursor {
                display: none;
            }

            .dots {
                transform: scale(.8);
            }
        }

        @media (max-width:600px) {
            .content {
                padding: 18px 16px 26px;
            }

            .error-visual {
                height: 270px;
            }

            .number {
                top: 0;
                font-size: clamp(130px, 38vw, 190px);
                letter-spacing: -9px;
            }

            .number .four {
                margin-right: 60px;
            }

            .number .three {
                margin-left: 60px;
            }

            .robot {
                width: 165px;
                height: 165px;
                border-radius: 48px;
            }

            .robot-dot {
                width: 8px;
                height: 8px;
            }

            .robot-dot:nth-child(1) {
                top: 19px;
                left: 20px;
            }

            .robot-dot:nth-child(2) {
                top: 19px;
                right: 20px;
            }

            .robot-dot:nth-child(3) {
                bottom: 19px;
                left: 20px;
            }

            .robot-dot:nth-child(4) {
                bottom: 19px;
                right: 20px;
            }

            .eye {
                width: 104px;
                height: 104px;
            }

            .iris {
                width: 55px;
                height: 55px;
            }

            .pupil {
                width: 27px;
                height: 30px;
            }

            .eye-glint {
                width: 13px;
                height: 13px;
                top: 8px;
                right: 10px;
            }

            .angry-lid {
                width: 128px;
                height: 78px;
                top: -31px;
            }

            .angry-brow {
                width: 68px;
                height: 10px;
                top: 5px;
            }

            .beacon {
                width: 39px;
                height: 35px;
                top: -36px;
                border-width: 3px;
            }

            .signal {
                top: -57px;
                width: 65px;
                height: 65px;
            }

            .ring {
                width: 225px;
                height: 225px;
            }

            .title {
                font-size: 25px;
                line-height: 1.45;
            }

            .title::after {
                height: 4px;
                margin-top: 9px;
            }

            .description {
                max-width: 355px;
                margin-top: 11px;
                font-size: 13px;
                line-height: 1.9;
            }

            .actions {
                margin-top: 15px;
            }

            .home-btn {
                min-width: 210px;
                padding: 12px 20px;
                font-size: 14px;
                border-radius: 12px;
            }

            .footer {
                margin-top: 16px;
                font-size: 10px;
            }

            .glow.one {
                width: 300px;
                height: 300px;
            }

            .glow.two {
                width: 280px;
                height: 280px;
            }

            .glow.three {
                width: 120px;
                height: 120px;
            }

            .dots {
                opacity: .35;
                transform: scale(.65);
            }

            .cross {
                transform: scale(.75);
                opacity: .25;
            }
        }

        @media (max-width:430px) {
            .content {
                padding: 12px 12px 22px;
            }

            .error-visual {
                height: 235px;
            }

            .number {
                font-size: 125px;
                letter-spacing: -8px;
            }

            .number .four {
                margin-right: 52px;
            }

            .number .three {
                margin-left: 52px;
            }

            .robot {
                width: 145px;
                height: 145px;
                border-width: 3px;
                border-radius: 42px;
            }

            .eye {
                width: 91px;
                height: 91px;
                border-width: 2px;
            }

            .iris {
                width: 48px;
                height: 48px;
            }

            .pupil {
                width: 24px;
                height: 27px;
            }

            .angry-lid {
                width: 113px;
                height: 68px;
                top: -27px;
            }

            .angry-brow {
                width: 59px;
                height: 9px;
                top: 4px;
            }

            .beacon {
                width: 34px;
                height: 30px;
                top: -31px;
            }

            .signal {
                top: -50px;
                width: 55px;
                height: 55px;
            }

            .ring {
                width: 195px;
                height: 195px;
            }

            .title {
                font-size: 22px;
            }

            .description {
                max-width: 310px;
                font-size: 12px;
                line-height: 1.85;
            }

            .home-btn {
                min-width: 195px;
                font-size: 13px;
                padding: 11px 17px;
            }

            .footer {
                font-size: 9px;
            }

            .corner {
                width: 25px;
                height: 25px;
            }
        }

        @media (max-width:360px) {
            .error-visual {
                height: 215px;
            }

            .number {
                font-size: 108px;
                letter-spacing: -7px;
            }

            .number .four {
                margin-right: 43px;
            }

            .number .three {
                margin-left: 43px;
            }

            .robot {
                width: 132px;
                height: 132px;
            }

            .eye {
                width: 82px;
                height: 82px;
            }

            .iris {
                width: 43px;
                height: 43px;
            }

            .pupil {
                width: 22px;
                height: 24px;
            }

            .angry-lid {
                width: 103px;
                height: 62px;
            }

            .angry-brow {
                width: 54px;
                height: 8px;
            }

            .title {
                font-size: 20px;
            }

            .description {
                max-width: 285px;
                font-size: 11px;
            }

            .home-btn {
                min-width: 180px;
                font-size: 12px;
            }
        }

        @media (max-height:760px) and (min-width:801px) {
            .content {
                padding-top: 15px;
                padding-bottom: 15px;
            }

            .error-visual {
                height: 300px;
            }

            .number {
                top: -4px;
                font-size: clamp(175px, 25vw, 285px);
            }

            .robot {
                width: 195px;
                height: 195px;
            }

            .eye {
                width: 120px;
                height: 120px;
            }

            .iris {
                width: 64px;
                height: 64px;
            }

            .title {
                font-size: 34px;
            }

            .description {
                margin-top: 10px;
                line-height: 1.8;
            }

            .actions {
                margin-top: 14px;
            }

            .footer {
                margin-top: 14px;
            }
        }

        @media (max-height:650px) and (min-width:601px) {
            .error-visual {
                height: 245px;
            }

            .number {
                font-size: 190px;
            }

            .robot {
                width: 155px;
                height: 155px;
                border-radius: 45px;
            }

            .eye {
                width: 100px;
                height: 100px;
            }

            .iris {
                width: 54px;
                height: 54px;
            }

            .pupil {
                width: 27px;
                height: 29px;
            }

            .title {
                font-size: 27px;
            }

            .description {
                font-size: 13px;
                margin-top: 7px;
            }

            .actions {
                margin-top: 9px;
            }
        }

        @media(prefers-reduced-motion:reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

    <div class="page">

        <div class="background">

            <div class="glow one"></div>
            <div class="glow two"></div>
            <div class="glow three"></div>

            <div class="decor circle a"></div>
            <div class="decor circle b"></div>
            <div class="decor circle c"></div>

            <div class="cross one"></div>
            <div class="cross two"></div>
            <div class="cross three"></div>

            <div class="dots one"></div>
            <div class="dots two"></div>

            <div class="wave"></div>
            <div class="wave two"></div>
            <div class="wave three"></div>

        </div>

        <main class="content">

            <div class="error-visual">

                <div class="number" dir="ltr">

                    <span class="four">4</span>

                    <span class="zero">0</span>

                    <span class="three">3</span>

                </div>

                <div class="ring"></div>

                <div class="robot">

                    <div class="robot-dot"></div>
                    <div class="robot-dot"></div>
                    <div class="robot-dot"></div>
                    <div class="robot-dot"></div>

                    <div class="signal"></div>

                    <div class="signal two"></div>

                    <div class="beacon"></div>

                    <div class="eye">

                        <div class="eyeball">

                            <div class="iris">

                                <div class="pupil"></div>

                                <div class="eye-glint"></div>

                            </div>

                        </div>

                        <div class="angry-lid"></div>

                        <div class="angry-brow"></div>

                    </div>

                </div>

            </div>

            <h1 class="title">
                ليس لديك الصلاحية للدخول
            </h1>

            <p class="description">
                عذرًا ، ليس لديك الصلاحية للوصول إلى هذه الصفحة
                <br>
                إذا كنت تعتقد أن هذا حدث عن طريق الخطأ ، يرجى التواصل مع المسؤول
            </p>

            <div class="actions">

                <a href="../../../index.php" class="home-btn">

                    <svg
                        class="home-icon"
                        viewBox="0 0 24 24"
                        aria-hidden="true">

                        <path d="
                        M12 3.2
                        3 10
                        v10.2
                        c0 .45 .35 .8 .8 .8
                        H9
                        v-6
                        h6
                        v6
                        h5.2
                        c.45 0 .8-.35 .8-.8
                        V10
                        l-9-6.8
                        Z

                        M12 1
                        l11 8.2
                        -1.2 1.6
                        L20 9.4
                        v10.8
                        c0 1.1-.9 2-2 2
                        h-4
                        v-6
                        h-4
                        v6
                        H6
                        c-1.1 0-2-.9-2-2
                        V9.4
                        L2.2 10.8
                        1 9.2
                        12 1
                        Z
                    " />

                    </svg>

                    العودة إلى الصفحة الرئيسية

                </a>

            </div>

            <div class="footer">
                جميع الحقوق محفوظة منصة كورساتك © 2026
            </div>

        </main>

    </div>

    <script>
        const eye = document.querySelector(".eye");
        const iris = document.querySelector(".iris");

        let targetX = 0;
        let targetY = 0;
        let currentX = 0;
        let currentY = 0;

        function updateEyeTarget(clientX, clientY) {
            const rect = eye.getBoundingClientRect();
            const centerX = rect.left + rect.width * 0.5;
            const centerY = rect.top + rect.height * 0.5;

            const dx = clientX - centerX;
            const dy = clientY - centerY;

            const angle = Math.atan2(dy, dx);
            const distance = Math.hypot(dx, dy);

            const maxDistance = 27;
            const strength = Math.min(distance / 170, 1);
            const move = maxDistance * strength;

            targetX = Math.cos(angle) * move;
            targetY = Math.sin(angle) * move * 0.82;
        }

        document.addEventListener("mousemove", event => {
            updateEyeTarget(event.clientX, event.clientY);
        });

        document.addEventListener("mouseleave", () => {
            targetX = 0;
            targetY = 0;
        });

        function animateEye() {
            currentX += (targetX - currentX) * 0.085;
            currentY += (targetY - currentY) * 0.085;

            iris.style.transform =
                `translate3d(calc(-50% + ${currentX}px), calc(-50% + ${currentY}px), 0)`;

            requestAnimationFrame(animateEye);
        }

        animateEye();

        window.addEventListener("resize", () => {
            targetX = 0;
            targetY = 0;
        });
    </script>

</body>

</html>