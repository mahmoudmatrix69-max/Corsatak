<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard Header</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

:root{
    --page:#f5f7fb;
    --surface:#ffffff;
    --surface-soft:#f8f9fc;

    --text:#171a23;
    --muted:#747b89;

    --border:#e5e8ee;

    --primary:#635bff;
    --primary-hover:#554ce8;
    --primary-soft:#f0eeff;
    --primary-border:#ddd9ff;

    --danger:#e95368;

    --shadow:
        0 12px 35px rgba(25,30,45,.09);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    min-height:100vh;
    background:var(--page);
    color:var(--text);
    font-family:"Inter",sans-serif;
    -webkit-font-smoothing:antialiased;
}

/* ========================================
   HEADER
======================================== */

.student-header{
    width:100%;
    height:68px;

    position:sticky;
    top:0;
    z-index:1000;

    background:var(--surface);

    border-bottom:1px solid var(--border);
}

.header-inner{
    width:100%;
    height:100%;

    padding:0 22px;

    display:flex;
    align-items:center;
    justify-content:space-between;
}

/* ========================================
   BRAND
======================================== */

.brand{
    display:flex;
    align-items:center;
    gap:11px;

    text-decoration:none;
    color:var(--text);

    flex-shrink:0;
}

.brand-icon{
    width:38px;
    height:38px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:10px;

    background:
        linear-gradient(
            135deg,
            #635bff,
            #827bff
        );

    color:#fff;

    font-size:16px;

    box-shadow:
        0 6px 16px rgba(99,91,255,.22);
}

.brand-name{
    font-size:16px;
    font-weight:800;

    letter-spacing:-.45px;
}

/* ========================================
   RIGHT AREA
======================================== */

.header-actions{
    display:flex;
    align-items:center;

    gap:9px;
}

/* ========================================
   WALLET
======================================== */

.wallet{
    height:44px;

    padding:4px 5px 4px 8px;

    display:flex;
    align-items:center;

    gap:8px;

    background:var(--primary-soft);

    border:1px solid var(--primary-border);

    border-radius:12px;

    transition:.2s ease;
}

.wallet:hover{
    border-color:#cfcaff;
}

.wallet-icon{
    width:30px;
    height:30px;

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;

    background:var(--primary);

    color:#fff;

    border-radius:8px;

    font-size:11px;
}

.wallet-info{
    display:flex;
    flex-direction:column;

    justify-content:center;

    gap:3px;

    min-width:72px;
}

.wallet-label{
    font-size:8px;

    color:var(--muted);

    font-weight:600;

    line-height:1;
}

.wallet-balance{
    font-size:12px;

    color:var(--text);

    font-weight:800;

    line-height:1;
}

.wallet-add{
    width:29px;
    height:29px;

    border:0;

    border-radius:8px;

    background:var(--primary);

    color:#fff;

    display:flex;
    align-items:center;
    justify-content:center;

    cursor:pointer;

    font-size:10px;

    transition:.2s ease;
}

.wallet-add:hover{
    background:var(--primary-hover);

    transform:scale(1.06);
}

/* ========================================
   ACCOUNT
======================================== */

.account-wrapper{
    position:relative;
}

.account{
    height:48px;

    padding:4px 9px 4px 4px;

    display:flex;
    align-items:center;

    gap:9px;

    background:var(--surface);

    border:1px solid var(--border);

    border-radius:13px;

    cursor:pointer;

    transition:.2s ease;
}

.account:hover{
    border-color:#d7dae2;

    box-shadow:
        0 7px 22px rgba(25,30,45,.06);
}

.avatar{
    width:38px;
    height:38px;

    border-radius:10px;

    overflow:hidden;

    flex-shrink:0;

    background:#e4e6eb;
}

.avatar img{
    width:100%;
    height:100%;

    object-fit:cover;

    display:block;
}

.account-info{
    display:flex;
    flex-direction:column;

    gap:4px;

    min-width:105px;
}

.account-name{
    font-size:11px;

    line-height:1;

    font-weight:800;

    color:var(--text);

    white-space:nowrap;
}

.account-role{
    font-size:8px;

    line-height:1;

    color:var(--muted);

    font-weight:500;

    white-space:nowrap;
}

.account-arrow{
    margin-left:2px;

    color:var(--muted);

    font-size:9px;

    transition:.2s ease;
}

.account-wrapper.open .account-arrow{
    transform:rotate(180deg);
}

/* ========================================
   ACCOUNT MENU
======================================== */

.account-menu{
    position:absolute;

    top:58px;
    right:0;

    width:310px;

    padding:9px;

    background:var(--surface);

    border:1px solid var(--border);

    border-radius:17px;

    box-shadow:var(--shadow);

    opacity:0;
    visibility:hidden;

    transform:
        translateY(-8px)
        scale(.98);

    transform-origin:top right;

    transition:
        opacity .2s ease,
        visibility .2s ease,
        transform .2s ease;
}

.account-wrapper.open .account-menu{
    opacity:1;

    visibility:visible;

    transform:
        translateY(0)
        scale(1);
}

/* ========================================
   MENU PROFILE
======================================== */

.menu-profile{
    padding:11px;

    display:flex;
    align-items:center;

    gap:11px;
}

.menu-avatar{
    width:44px;
    height:44px;

    flex-shrink:0;

    border-radius:11px;

    overflow:hidden;

    background:#e4e6eb;
}

.menu-avatar img{
    width:100%;
    height:100%;

    object-fit:cover;
}

.menu-name{
    font-size:12px;

    font-weight:800;

    color:var(--text);

    line-height:1.2;
}

.menu-email{
    margin-top:5px;

    font-size:9px;

    color:var(--muted);

    line-height:1.2;
}

/* ========================================
   MENU WALLET
======================================== */

.menu-wallet{
    margin:3px;

    padding:13px;

    background:var(--primary-soft);

    border:1px solid var(--primary-border);

    border-radius:12px;
}

.menu-wallet-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.menu-wallet-title{
    display:flex;
    align-items:center;

    gap:7px;

    color:var(--muted);

    font-size:10px;

    font-weight:600;
}

.menu-wallet-title i{
    color:var(--primary);
}

.menu-wallet-balance{
    margin-top:7px;

    font-size:19px;

    line-height:1;

    font-weight:800;

    color:var(--text);
}

.menu-add{
    width:100%;
    height:33px;

    margin-top:10px;

    border:0;

    border-radius:8px;

    background:var(--primary);

    color:#fff;

    font-size:10px;

    font-weight:700;

    cursor:pointer;

    transition:.2s ease;
}

.menu-add:hover{
    background:var(--primary-hover);
}

/* ========================================
   DIVIDER
======================================== */

.divider{
    width:100%;
    height:1px;

    background:var(--border);

    margin:7px 0;
}

/* ========================================
   MENU LINKS
======================================== */

.menu-link{
    min-height:38px;

    padding:0 11px;

    display:flex;
    align-items:center;

    gap:11px;

    border-radius:9px;

    text-decoration:none;

    color:var(--text);

    font-size:10px;

    font-weight:600;

    transition:.2s ease;
}

.menu-link i{
    width:17px;

    text-align:center;

    color:var(--muted);

    font-size:11px;
}

.menu-link:hover{
    background:var(--surface-soft);

    color:var(--primary);
}

.menu-link:hover i{
    color:var(--primary);
}

.menu-link.logout{
    color:var(--danger);
}

.menu-link.logout i{
    color:var(--danger);
}

/* ========================================
   SETTINGS ROW
======================================== */

.menu-setting{
    min-height:38px;

    padding:0 11px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    border-radius:9px;

    font-size:10px;

    font-weight:600;
}

.setting-left{
    display:flex;
    align-items:center;

    gap:11px;
}

.setting-left i{
    width:17px;

    text-align:center;

    color:var(--muted);

    font-size:11px;
}

/* ========================================
   SWITCH
======================================== */

.switch{
    width:32px;
    height:18px;

    padding:2px;

    border:0;

    border-radius:20px;

    background:#dfe2e8;

    cursor:pointer;

    transition:.2s ease;
}

.switch span{
    width:14px;
    height:14px;

    display:block;

    background:#fff;

    border-radius:50%;

    transition:.2s ease;
}

.switch.active{
    background:var(--primary);
}

.switch.active span{
    transform:translateX(14px);
}

/* ========================================
   RESPONSIVE
======================================== */

@media(max-width:650px){

    .student-header{
        height:62px;
    }

    .header-inner{
        padding:0 12px;
    }

    .brand-icon{
        width:35px;
        height:35px;

        font-size:14px;
    }

    .brand-name{
        font-size:14px;
    }

    .header-actions{
        gap:6px;
    }

    .wallet{
        height:39px;

        padding:3px;

        gap:5px;
    }

    .wallet-icon{
        width:29px;
        height:29px;
    }

    .wallet-info{
        min-width:65px;
    }

    .wallet-label{
        font-size:7px;
    }

    .wallet-balance{
        font-size:10px;
    }

    .wallet-add{
        width:26px;
        height:26px;
    }

    .account{
        height:43px;

        padding:3px 7px 3px 3px;

        gap:7px;
    }

    .avatar{
        width:35px;
        height:35px;
    }

    .account-info{
        min-width:85px;
    }

    .account-name{
        font-size:10px;
    }

    .account-role{
        font-size:7px;
    }

    .account-menu{
        position:fixed;

        top:70px;

        left:10px;
        right:10px;

        width:auto;

        max-height:
            calc(100vh - 82px);

        overflow-y:auto;

        transform-origin:top center;
    }
}

/* ========================================
   SMALL PHONES
======================================== */

@media(max-width:450px){

    .header-inner{
        padding:0 9px;
    }

    .brand-name{
        font-size:13px;
    }

    .wallet{
        width:38px;
        height:38px;

        justify-content:center;

        padding:3px;
    }

    .wallet-info,
    .wallet-add{
        display:none;
    }

    .wallet-icon{
        width:30px;
        height:30px;
    }

    .account{
        width:41px;
        height:38px;

        padding:3px;

        justify-content:center;
    }

    .avatar{
        width:32px;
        height:32px;
    }

    .account-info,
    .account-arrow{
        display:none;
    }
}

/* ========================================
   EXTRA SMALL
======================================== */

@media(max-width:350px){

    .brand-name{
        display:none;
    }

    .brand-icon{
        width:33px;
        height:33px;
    }

    .wallet{
        width:36px;
        height:36px;
    }

    .wallet-icon{
        width:28px;
        height:28px;
    }

    .account{
        width:38px;
        height:36px;
    }

    .avatar{
        width:30px;
        height:30px;
    }
}

</style>
</head>

<body>

<header class="student-header">

    <div class="header-inner">

        <!-- BRAND -->

        <a href="#" class="brand">

            <div class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>

            <span class="brand-name">
                EduSpace
            </span>

        </a>


        <!-- RIGHT -->

        <div class="header-actions">

            <!-- WALLET -->

            <div class="wallet">

                <div class="wallet-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>

                <div class="wallet-info">

                    <span class="wallet-label">
                        Available Balance
                    </span>

                    <span class="wallet-balance">
                        $250.00
                    </span>

                </div>

                <button
                    class="wallet-add"
                    aria-label="Add money"
                >
                    <i class="fa-solid fa-plus"></i>
                </button>

            </div>


            <!-- ACCOUNT -->

            <div
                class="account-wrapper"
                id="accountWrapper"
            >

                <div
                    class="account"
                    id="accountButton"
                >

                    <div class="avatar">

                        <img
                            src="https://i.pravatar.cc/150?img=12"
                            alt="Mahmoud Hussein"
                        >

                    </div>

                    <div class="account-info">

                        <span class="account-name">
                            Mahmoud Hussein
                        </span>

                        <span class="account-role">
                            Student Account
                        </span>

                    </div>

                    <i class="fa-solid fa-chevron-down account-arrow"></i>

                </div>


                <!-- MENU -->

                <div class="account-menu">

                    <div class="menu-profile">

                        <div class="menu-avatar">

                            <img
                                src="https://i.pravatar.cc/150?img=12"
                                alt="Mahmoud Hussein"
                            >

                        </div>

                        <div>

                            <div class="menu-name">
                                Mahmoud Hussein
                            </div>

                            <div class="menu-email">
                                student@example.com
                            </div>

                        </div>

                    </div>


                    <div class="menu-wallet">

                        <div class="menu-wallet-top">

                            <div class="menu-wallet-title">

                                <i class="fa-solid fa-wallet"></i>

                                Wallet Balance

                            </div>

                            <i class="fa-solid fa-ellipsis"></i>

                        </div>

                        <div class="menu-wallet-balance">
                            $250.00
                        </div>

                        <button class="menu-add">

                            <i class="fa-solid fa-plus"></i>

                            Add Funds

                        </button>

                    </div>


                    <div class="divider"></div>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-house"></i>

                        Dashboard

                    </a>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-book-open"></i>

                        My Courses

                    </a>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-compass"></i>

                        Explore Courses

                    </a>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-chart-line"></i>

                        Learning Progress

                    </a>


                    <div class="divider"></div>


                    <a href="#" class="menu-link">

                        <i class="fa-regular fa-bell"></i>

                        Notifications

                    </a>


                    <div class="menu-setting">

                        <div class="setting-left">

                            <i class="fa-solid fa-moon"></i>

                            <span>Dark Mode</span>

                        </div>

                        <button
                            class="switch"
                            id="darkSwitch"
                            aria-label="Dark mode"
                        >

                            <span></span>

                        </button>

                    </div>


                    <div class="divider"></div>


                    <a href="#" class="menu-link">

                        <i class="fa-regular fa-user"></i>

                        My Profile

                    </a>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-receipt"></i>

                        Transactions

                    </a>


                    <a href="#" class="menu-link">

                        <i class="fa-solid fa-gear"></i>

                        Account Settings

                    </a>


                    <div class="divider"></div>


                    <a href="#" class="menu-link logout">

                        <i class="fa-solid fa-right-from-bracket"></i>

                        Sign Out

                    </a>

                </div>

            </div>

        </div>

    </div>

</header>


<script>

/* ========================================
   ACCOUNT MENU
======================================== */

const accountWrapper =
    document.getElementById("accountWrapper");

const accountButton =
    document.getElementById("accountButton");

accountButton.addEventListener("click", function(event){

    event.stopPropagation();

    accountWrapper.classList.toggle("open");

});

document.addEventListener("click", function(event){

    if(!accountWrapper.contains(event.target)){

        accountWrapper.classList.remove("open");

    }

});


/* ========================================
   DARK MODE
======================================== */

const darkSwitch =
    document.getElementById("darkSwitch");

darkSwitch.addEventListener("click", function(event){

    event.stopPropagation();

    this.classList.toggle("active");

    const dark =
        this.classList.contains("active");

    if(dark){

        document.documentElement.style.setProperty(
            "--page",
            "#0e1117"
        );

        document.documentElement.style.setProperty(
            "--surface",
            "#151a22"
        );

        document.documentElement.style.setProperty(
            "--surface-soft",
            "#1b212b"
        );

        document.documentElement.style.setProperty(
            "--text",
            "#f4f6f9"
        );

        document.documentElement.style.setProperty(
            "--muted",
            "#9098a7"
        );

        document.documentElement.style.setProperty(
            "--border",
            "#282f3a"
        );

    }else{

        document.documentElement.style.setProperty(
            "--page",
            "#f5f7fb"
        );

        document.documentElement.style.setProperty(
            "--surface",
            "#ffffff"
        );

        document.documentElement.style.setProperty(
            "--surface-soft",
            "#f8f9fc"
        );

        document.documentElement.style.setProperty(
            "--text",
            "#171a23"
        );

        document.documentElement.style.setProperty(
            "--muted",
            "#747b89"
        );

        document.documentElement.style.setProperty(
            "--border",
            "#e5e8ee"
        );

    }

});

</script>

</body>
</html>