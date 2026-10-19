<?php

require_once __DIR__ . '/paths.php';
require_once $config;
require_once $session;

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>قسم الطالب</title>

    <!-- الخط -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>
*{margin:0;padding:0;box-sizing:border-box}

:root{
    --blue:#2563eb;
    --blue-dark:#1d4ed8;
    --text:#172033;
    --muted:#7b8494;
    --border:#e7ebf2;
}

html,body{min-height:100%}

body{
    min-height:100vh;
    font-family:"Cairo",sans-serif;
    background:#f8faff;
    color:var(--text);
    overflow-x:hidden;
}

.page{
    min-height:100vh;
    display:flex;
    flex-direction:column;
    position:relative;
    isolation:isolate;
    overflow:hidden;
}

.page:before,
.page:after{
    content:"";
    position:absolute;
    width:430px;
    height:430px;
    border-radius:50%;
    pointer-events:none;
    z-index:-1;
    filter:blur(2px);
}

.page:before{
    top:-285px;
    left:-180px;
    background:rgba(37,99,235,.055);
    animation:ambient 9s ease-in-out infinite;
}

.page:after{
    right:-210px;
    bottom:-250px;
    background:rgba(79,70,229,.045);
    animation:ambient 11s ease-in-out infinite reverse;
}

@keyframes ambient{
    0%,100%{transform:translate(0,0) scale(1)}
    50%{transform:translate(14px,-10px) scale(1.04)}
}

/* =========================
   الهيدر
========================= */

header{
    width:100%;
    height:72px;
    padding:0 clamp(16px,4vw,42px);
    display:flex;
    align-items:center;
    justify-content:space-between;
    border-bottom:1px solid rgba(226,232,240,.82);
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(14px);
    -webkit-backdrop-filter:blur(14px);
    position:relative;
    z-index:10;
    animation:headerIn .55s cubic-bezier(.2,.8,.2,1) both;
}

@keyframes headerIn{
    from{opacity:0;transform:translateY(-12px)}
    to{opacity:1;transform:translateY(0)}
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    color:var(--text);
    text-decoration:none;
    font-size:17px;
    font-weight:800;
    letter-spacing:-.2px;
}

.logo-icon{
    width:38px;
    height:38px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:12px;
    background:linear-gradient(135deg,#2563eb,#4f46e5);
    color:#fff;
    box-shadow:0 7px 18px rgba(37,99,235,.18);
    transition:transform .25s ease,box-shadow .25s ease;
}

.logo-icon svg{width:19px;height:19px}

.logo:hover .logo-icon{
    transform:translateY(-2px) rotate(-3deg);
    box-shadow:0 11px 24px rgba(37,99,235,.25);
}

.logout{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    height:39px;
    padding:0 14px;
    border-radius:11px;
    background:#fff;
    border:1px solid var(--border);
    color:#596579;
    text-decoration:none;
    font-size:12px;
    font-weight:700;
    box-shadow:0 3px 12px rgba(15,23,42,.025);
    transition:color .22s ease,background .22s ease,border-color .22s ease,transform .22s ease,box-shadow .22s ease;
}

.logout svg{
    width:16px;
    height:16px;
    transition:transform .22s ease;
}

.logout:hover{
    color:#dc2626;
    background:#fffafa;
    border-color:#fecaca;
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(220,38,38,.07);
}

.logout:hover svg{transform:translateX(-2px)}
.logout:active{transform:translateY(0) scale(.98)}

/* =========================
   المحتوى
========================= */

main{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 20px;
}

.content{
    width:100%;
    max-width:560px;
    text-align:center;
    animation:contentIn .7s cubic-bezier(.2,.8,.2,1) both;
}

@keyframes contentIn{
    from{opacity:0;transform:translateY(22px)}
    to{opacity:1;transform:translateY(0)}
}

/* =========================
   الأيقونة
========================= */

.icon-box{
    width:82px;
    height:82px;
    margin:0 auto 21px;
    border-radius:24px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(145deg,#eef5ff,#e4eeff);
    color:var(--blue);
    box-shadow:0 15px 35px rgba(37,99,235,.09);
    position:relative;
    animation:iconFloat 4s ease-in-out infinite;
}

.icon-box:before{
    content:"";
    position:absolute;
    inset:-7px;
    border:1px solid rgba(37,99,235,.09);
    border-radius:30px;
    animation:ring 3s ease-out infinite;
}

.icon-box svg{
    width:42px;
    height:42px;
    transition:transform .3s ease;
}

.icon-box:hover svg{transform:scale(1.08) rotate(4deg)}

@keyframes iconFloat{
    0%,100%{transform:translateY(0)}
    50%{transform:translateY(-7px)}
}

@keyframes ring{
    0%{opacity:.7;transform:scale(.96)}
    70%,100%{opacity:0;transform:scale(1.08)}
}

/* =========================
   النص
========================= */

.small-title{
    display:inline-block;
    margin-bottom:8px;
    color:var(--blue);
    font-size:12px;
    font-weight:700;
    animation:fadeUp .55s .1s ease both;
}

h1{
    font-size:clamp(28px,5vw,40px);
    line-height:1.35;
    font-weight:800;
    margin-bottom:11px;
    color:var(--text);
    animation:fadeUp .55s .18s ease both;
}

h1 span{
    color:var(--blue);
    display:inline-block;
    position:relative;
}

h1 span:after{
    content:"";
    position:absolute;
    height:3px;
    width:42%;
    right:0;
    bottom:-4px;
    border-radius:10px;
    background:#dbe8ff;
    transform-origin:right;
    animation:lineIn .7s .6s ease both;
}

@keyframes lineIn{
    from{transform:scaleX(0);opacity:0}
    to{transform:scaleX(1);opacity:1}
}

.description{
    color:var(--muted);
    font-size:14px;
    line-height:1.95;
    max-width:470px;
    margin:0 auto 22px;
    animation:fadeUp .55s .28s ease both;
}

.status{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:7px 13px;
    border-radius:30px;
    background:rgba(255,255,255,.92);
    border:1px solid var(--border);
    color:#667085;
    font-size:11px;
    font-weight:600;
    box-shadow:0 7px 24px rgba(25,50,90,.05);
    animation:fadeUp .55s .38s ease both;
}

.status-dot{
    width:7px;
    height:7px;
    border-radius:50%;
    background:#22c55e;
    box-shadow:0 0 0 5px rgba(34,197,94,.10);
    animation:dotPulse 1.8s ease-in-out infinite;
}

@keyframes dotPulse{
    0%,100%{transform:scale(1);opacity:1}
    50%{transform:scale(1.25);opacity:.72}
}

@keyframes fadeUp{
    from{opacity:0;transform:translateY(9px)}
    to{opacity:1;transform:translateY(0)}
}

footer{
    padding:0 20px 18px;
    text-align:center;
    color:#a0a8b5;
    font-size:10px;
    animation:fadeIn .8s .5s ease both;
}

@keyframes fadeIn{
    from{opacity:0}
    to{opacity:1}
}

/* =========================
   الموبايل
========================= */

@media(max-width:600px){

    header{
        height:64px;
        padding:0 14px;
    }

    .logo{
        gap:8px;
        font-size:15px;
    }

    .logo-icon{
        width:34px;
        height:34px;
        border-radius:10px;
    }

    .logo-icon svg{
        width:17px;
        height:17px;
    }

    .logout{
        height:35px;
        padding:0 10px;
        gap:6px;
        border-radius:9px;
        font-size:10px;
    }

    .logout svg{
        width:14px;
        height:14px;
    }

    main{padding:25px 16px 35px}

    .icon-box{
        width:70px;
        height:70px;
        border-radius:20px;
        margin-bottom:17px;
    }

    .icon-box:before{
        inset:-5px;
        border-radius:25px;
    }

    .icon-box svg{
        width:35px;
        height:35px;
    }

    .small-title{font-size:11px}

    h1{
        font-size:28px;
        margin-bottom:8px;
    }

    h1 span:after{
        height:2px;
        bottom:-3px;
    }

    .description{
        font-size:12px;
        line-height:1.95;
        max-width:335px;
    }

    .status{
        font-size:10px;
        padding:6px 11px;
    }

    footer{
        font-size:9px;
        padding-bottom:14px;
    }
}

@media(max-width:360px){

    h1{font-size:25px}

    .description{
        font-size:11px;
        max-width:300px;
    }

    .icon-box{
        width:64px;
        height:64px;
        border-radius:18px;
    }

    .icon-box svg{
        width:32px;
        height:32px;
    }
}

@media(prefers-reduced-motion:reduce){
    *,*:before,*:after{
        animation-duration:.01ms!important;
        animation-iteration-count:1!important;
        transition-duration:.01ms!important;
    }
}
</style>

</head>


<body>


<div class="page">


    <!-- الهيدر -->

    <header>


        <a href="#" class="logo">

            <div class="logo-icon">

                <svg
                    width="19"
                    height="19"
                    viewBox="0 0 24 24"
                    fill="none"
                >

                    <path
                        d="M4 8.5L12 4L20 8.5L12 13L4 8.5Z"
                        stroke="white"
                        stroke-width="1.8"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M7 10.5V15C7 15 9 17 12 17C15 17 17 15 17 15V10.5"
                        stroke="white"
                        stroke-width="1.8"
                    />

                </svg>

            </div>

            كورساتك

        </a>


        <!-- زر تسجيل الخروج -->

        <a href="logout.php" class="logout">

            <svg
                viewBox="0 0 24 24"
                fill="none"
            >

                <path
                    d="M10 17L15 12L10 7"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />

                <path
                    d="M15 12H3"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                />

                <path
                    d="M14 4H19C20.1 4 21 4.9 21 6V18C21 19.1 20.1 20 19 20H14"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                />

            </svg>

            تسجيل الخروج

        </a>


    </header>


    <!-- المحتوى -->

    <main>


        <div class="content">


            <!-- الأيقونة -->

            <div class="icon-box">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                >

                    <path
                        d="M12 3L14.5 8.2L20 9L16 13L17 18.5L12 16L7 18.5L8 13L4 9L9.5 8.2L12 3Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M9 21H15"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                    />

                </svg>

            </div>


            <div class="small-title">
                قريبًا
            </div>


            <h1>

                قسم الطالب

                <span>
                    تحت الإنشاء
                </span>

            </h1>


            <p class="description">

                نعمل على تجهيز قسم الطالب ليكون المكان
                الذي تتابع منه دراستك ودوراتك بسهولة.
                نراكم قريبًا ✨

            </p>


            <div class="status">

                <span class="status-dot"></span>

                جاري التطوير

            </div>


            

        </div>


    </main>





</div>


</body>

</html>
