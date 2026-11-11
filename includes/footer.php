<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Shared Footer
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

$officialBaseUrl =
    '/betachem/betachem.betaoffice.co.za/public_html';

$officialFooterLogo =
    $officialBaseUrl . '/assets/header_logo.png';

$privacyPolicy =
    $officialBaseUrl . '/assets/docs/Privacy%20Policy.pdf';

$betachemMap =
    $officialBaseUrl . '/assets/docs/Map.pdf';

?>

</main>


<style>

/* =========================================================
   FOOTER LAYOUT FIX
========================================================= */

html {
    min-height: 100vh;
}

main,
#app,
.site-wrapper,
.page-content,
.content-wrapper {
    flex: 1 0 auto;
}

footer,
.site-footer,
.footer-custom {
    flex-shrink: 0;
    margin: 0;
    position: relative;
    z-index: 2;
}

@media (max-width: 991.98px) {

    footer,
    .site-footer,
    .footer-custom {
        position: static !important;
    }

}

body {
    padding-bottom: 0 !important;
}

.min-vh-0 {
    min-height: 100vh !important;
}

.contact-hover {
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-hover:hover {
    text-decoration: underline;
}


/* =========================================================
   BETACHEM FOOTER
   DARK BLUE TO BLUE GRADIENT
========================================================= */

.footer-custom {
    background: linear-gradient(
        135deg,
        #2D208A 0%,
        #1745CA 50%,
        #1D88E9 100%
    ) !important;

    color: #fff !important;
}

.footer-custom a {
    color: #fff;
}

.footer-custom a:hover {
    color: #fff;
}


/* =========================================================
   FOOTER HEX
   MATCH OFFICIAL WEBSITE
========================================================= */

.footer-custom .footer-hex {
    background: none;
    clip-path: none;
    padding: 0;
    width: auto;
    height: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.footer-custom .footer-hex svg {
    width: 95px;
    height: 65px;
    display: block;

    transition:
        transform .3s ease,
        filter .3s ease;
}

.footer-custom .footer-hex:hover svg {
    transform: scale(1.2);

    filter:
        drop-shadow(
            0 0 8px rgba(255,255,255,0.45)
        );
}

.footer-custom .footer-hex svg path {
    stroke-width: 6;
}


/* =========================================================
   FOOTER TEXT
========================================================= */

.footer-custom h5,
.footer-custom h6 {
    font-family:
        'Raleway',
        Arial,
        sans-serif;

    font-weight: 900;
    color: #fff;
}

.footer-custom p {
    line-height: 1.55;
    color: #fff;
}

.footer-custom .text-white {
    color: #fff !important;
}


/* =========================================================
   FOOTER BUTTONS
========================================================= */

.footer-custom .btn-info {
    color: #fff !important;
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.footer-custom .btn-info:hover {
    color: #fff !important;
    background-color: #0aa7c2;
    border-color: #0aa7c2;
}


/* =========================================================
   FOOTER LOGO
========================================================= */

.footer-logo {
    max-height: 180px;
}


/* =========================================================
   MODAL
========================================================= */

#pdfModal .modal-title,
#mapModal .modal-title {
    color: #000;
}

#viewerWrap {
    background: #fff;
}

</style>


<!-- =========================================================
     BETACHEM FOOTER
========================================================= -->

<footer class="pt-3 mt-auto text-white footer-custom">


    <div class="container">

        <div class="text-white row">


            <!-- =====================================================
                 DESKTOP (LG+)
            ====================================================== -->

            <div class="d-none d-lg-flex w-100">

                <div class="row w-100 align-items-start">


                    <!-- LOGO -->

                    <div class="col-lg-1">

                        <div class="mb-3 text-center">

                            <img
                                src="<?= htmlspecialchars(
                                    $officialFooterLogo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="Footer Logo"
                                class="img-fluid footer-logo"
                            >

                        </div>

                    </div>


                    <!-- NUMBERS & EMAIL -->

                    <div class="text-center col-lg-3 fs-6">

                        <h5 class="mb-2">
                            Numbers &amp; Email
                        </h5>

                        <p class="mb-1">

                            t:
                            <a
                                href="tel:+27117828789"
                                class="text-white"
                            >
                                +27 11 782 8789
                            </a>

                            <br>

                            f:
                            <a
                                href="tel:+27118887458"
                                class="text-white"
                            >
                                +27 11 888 7458
                            </a>

                            <br>

                            m:
                            <a
                                href="tel:+27728045528"
                                class="text-white"
                            >
                                +27 72 804 5528
                            </a>

                            <br>

                            <a
                                href="mailto:contact@bchem.co.za"
                                class="text-white"
                            >
                                contact@bchem.co.za
                            </a>

                        </p>

                    </div>


                    <!-- PHYSICAL ADDRESSES -->

                    <div class="col-lg-4">

                        <div class="row">

                            <h5 class="mb-3 text-center">
                                Physical Addresses
                            </h5>


                            <div class="text-center col-sm-6">

                                <h6 class="text-uppercase">
                                    Administration Office
                                </h6>

                                <p class="mb-3 small">

                                    31 Pafuri Road
                                    <br>

                                    Emmarentia
                                    <br>

                                    Johannesburg
                                    <br>

                                    South Africa
                                    <br>

                                </p>

                            </div>


                            <div class="text-center col-sm-6">

                                <h6 class="text-uppercase">
                                    Factory &amp; Laboratory
                                </h6>

                                <p class="mb-3 small">

                                    Plot 5, Producta
                                    <br>

                                    Heidelberg
                                    <br>

                                    Gauteng
                                    <br>

                                    <a
                                        href="https://www.google.com/maps?q=-26.414592,28.353447"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style="color:#fff !important;"
                                    >
                                        -26.414592, 28.353447
                                    </a>

                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- RESOURCES -->

                    <div class="text-center col-lg-4">

                        <a
                            href="<?= htmlspecialchars(
                                $officialBaseUrl . '/resources',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="footer-hex hex-btn nav-link"
                            aria-label="Resources"
                        >

                            <svg
                                version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 200 173.205"
                                preserveAspectRatio="xMidYMid meet"
                            >

                                <path
                                    d="M0 86.6025L50 0L150 0L200 86.6025L150 173.205L50 173.205Z"
                                    fill="#fff"
                                    stroke="#1D88E9"
                                    stroke-width="6"
                                ></path>

                                <text
                                    x="100"
                                    y="90"
                                    text-anchor="middle"
                                    dominant-baseline="middle"
                                    font-family="Raleway, Arial, sans-serif"
                                    font-weight="900"
                                    font-size="26"
                                    fill="#2D208A"
                                >
                                    Resources
                                </text>

                            </svg>

                        </a>


                        <div class="mt-4 mb-0 text-center row">


                            <!-- PRIVACY POLICY -->

                            <div class="col-4">

                                <a
                                    href="<?= htmlspecialchars(
                                        $privacyPolicy,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="btn btn-sm btn-info badge badge-pill badge-info doc-link"
                                    data-title="Privacy Policy"
                                    data-viewer="google"
                                >
                                    Privacy Policy
                                </a>

                            </div>


                            <!-- COURIER ADDRESS -->

                            <div class="col-4">

                                <a
                                    href="#"
                                    class="btn btn-sm btn-info badge badge-pill badge-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#mapModal"
                                >
                                    Courier Address
                                </a>

                            </div>


                            <!-- BETACHEM MAP -->

                            <div class="col-4">

                                <a
                                    href="<?= htmlspecialchars(
                                        $betachemMap,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="btn btn-sm btn-info badge badge-pill badge-info doc-link"
                                    data-title="Betachem Map"
                                    data-viewer="google"
                                >
                                    Betachem Map
                                </a>

                            </div>


                        </div>

                    </div>


                </div>

            </div>


            <!-- =====================================================
                 TABLET (MD ONLY)
            ====================================================== -->

            <div class="d-none d-md-block d-lg-none w-100">

                <div class="row align-items-start">


                    <!-- NUMBERS -->

                    <div class="col-md-4 text-center fs-6">

                        <h5 class="mb-2">
                            Numbers &amp; Email
                        </h5>

                        <p class="mb-1">

                            t:
                            <a
                                href="tel:+27117828789"
                                class="text-white"
                            >
                                +27 11 782 8789
                            </a>

                            <br>

                            f:
                            <a
                                href="tel:+27118887458"
                                class="text-white"
                            >
                                +27 11 888 7458
                            </a>

                            <br>

                            m:
                            <a
                                href="tel:+27728045528"
                                class="text-white"
                            >
                                +27 72 804 5528
                            </a>

                            <br>

                            <a
                                href="mailto:contact@bchem.co.za"
                                class="text-white"
                            >
                                contact@bchem.co.za
                            </a>

                        </p>

                    </div>


                    <!-- ADDRESSES -->

                    <div class="col-md-8">

                        <div class="row">

                            <h5 class="mb-2 text-center">
                                Physical Addresses
                            </h5>


                            <div class="text-center col-6">

                                <h6 class="text-uppercase">
                                    Administration Office
                                </h6>

                                <p class="mb-2 small">

                                    31 Pafuri Road
                                    <br>

                                    Emmarentia
                                    <br>

                                    Johannesburg
                                    <br>

                                    South Africa
                                    <br>

                                </p>

                            </div>


                            <div class="text-center col-6">

                                <h6 class="text-uppercase">
                                    Factory &amp; Laboratory
                                </h6>

                                <p class="mb-2 small">

                                    Plot 5, Producta
                                    <br>

                                    Heidelberg
                                    <br>

                                    Gauteng
                                    <br>

                                    <a
                                        href="https://www.google.com/maps?q=-26.414592,28.353447"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style="color:#fff !important;"
                                    >
                                        -26.414592, 28.353447
                                    </a>

                                </p>

                            </div>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =====================================================
                 MOBILE (SM AND DOWN)
            ====================================================== -->

            <div class="d-block d-md-none w-100 text-center fs-6">

                <h5 class="mb-2">
                    Numbers &amp; Email
                </h5>

                <p class="mb-1">

                    t:
                    <a
                        href="tel:+27117828789"
                        class="text-white"
                    >
                        +27 11 782 8789
                    </a>

                    <br>

                    f:
                    <a
                        href="tel:+27118887458"
                        class="text-white"
                    >
                        +27 11 888 7458
                    </a>

                    <br>

                    m:
                    <a
                        href="tel:+27728045528"
                        class="text-white"
                    >
                        +27 72 804 5528
                    </a>

                    <br>

                    <a
                        href="mailto:contact@bchem.co.za"
                        class="text-white"
                    >
                        contact@bchem.co.za
                    </a>

                </p>

            </div>


        </div>

    </div>


    <!-- =========================================================
         GLOBAL DOCUMENT VIEWER MODAL
    ========================================================== -->

    <div
        class="modal fade"
        id="pdfModal"
        tabindex="-1"
        aria-labelledby="pdfTitle"
        aria-hidden="true"
    >

        <div
            class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
        >

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        id="pdfTitle"
                        class="text-black modal-title"
                    >
                        Document
                    </h5>

                    <a
                        id="downloadBtn"
                        href="#"
                        class="btn btn-sm me-2"
                        download
                    >
                        <i class="fa fa-download fs-3"></i>
                    </a>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>


                <div
                    id="viewerWrap"
                    class="position-relative"
                    style="min-height:70vh;"
                >

                    <div
                        class="position-absolute top-50 start-50 translate-middle"
                        id="viewerLoader"
                        aria-hidden="true"
                    >

                        <div
                            class="spinner-border"
                            role="status"
                            aria-label="Loading"
                        ></div>

                    </div>


                    <iframe
                        id="pdfFrame"
                        title="Embedded document viewer"
                        style="width:100%;height:70vh;border:0;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         GOOGLE MAP MODAL
    ========================================================== -->

    <div
        class="modal fade"
        id="mapModal"
        tabindex="-1"
        aria-labelledby="mapModalLabel"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-xl modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="mapModalLabel"
                    >
                        Factory &amp; Laboratory – GPS Location
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>


                <div class="p-0 modal-body">

                    <iframe
                        width="100%"
                        height="600"
                        frameborder="0"
                        style="border:0"
                        src="https://www.google.com/maps?q=-26.414592,28.353447&hl=en&z=15&output=embed"
                        allowfullscreen
                    ></iframe>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         FOOTER JAVASCRIPT
    ========================================================== -->

    <script>

    /*
    |--------------------------------------------------------------------------
    | Resource Cards Lazy Loading
    |--------------------------------------------------------------------------
    */

    (function () {

        const cards =
            document.querySelectorAll('.resource-card');


        const setBG = function (el) {

            const bg =
                el.getAttribute('data-bg');

            if (
                bg &&
                !el.dataset.loaded
            ) {

                el.style.backgroundImage =
                    "url('" + bg + "')";

                el.dataset.loaded = '1';

            }

        };


        if (
            !('IntersectionObserver' in window)
        ) {

            cards.forEach(setBG);

        } else {

            const io =
                new IntersectionObserver(
                    function (entries, observer) {

                        entries.forEach(
                            function (entry) {

                                if (
                                    entry.isIntersecting
                                ) {

                                    setBG(entry.target);

                                    observer.unobserve(
                                        entry.target
                                    );

                                }

                            }
                        );

                    },
                    {
                        threshold: 0.15
                    }
                );


            cards.forEach(
                function (el) {
                    io.observe(el);
                }
            );

        }

    })();


    /*
    |--------------------------------------------------------------------------
    | Document Viewer
    |--------------------------------------------------------------------------
    */

    (function () {

        const triggers =
            document.querySelectorAll(
                '.pdf-link, .doc-link'
            );


        const modalEl =
            document.getElementById(
                'pdfModal'
            );


        if (
            !modalEl ||
            !triggers.length
        ) {
            return;
        }


        const modal =
            new bootstrap.Modal(
                modalEl
            );


        const frame =
            document.getElementById(
                'pdfFrame'
            );


        const viewerWrap =
            document.getElementById(
                'viewerWrap'
            );


        const titleEl =
            document.getElementById(
                'pdfTitle'
            );


        const downloadBtn =
            document.getElementById(
                'downloadBtn'
            );


        function getViewerURL(
            fileUrl,
            override
        ) {

            const url =
                String(fileUrl || '').trim();


            if (!url) {
                return '';
            }


            const match =
                url
                    .split('?')[0]
                    .split('#')[0]
                    .match(/\.([a-z0-9]+)$/i);


            const ext =
                match
                    ? match[1].toLowerCase()
                    : '';


            const officeExts = [
                'doc',
                'docx',
                'xls',
                'xlsx',
                'ppt',
                'pptx'
            ];


            if (
                override === 'office'
            ) {

                return (
                    'https://view.officeapps.live.com/op/embed.aspx?src='
                    +
                    encodeURIComponent(url)
                );

            }


            if (
                override === 'google'
            ) {

                return (
                    'https://docs.google.com/viewer?embedded=true&url='
                    +
                    encodeURIComponent(url)
                );

            }


            if (
                override === 'native'
            ) {

                return url;

            }


            if (
                officeExts.includes(ext)
            ) {

                return (
                    'https://view.officeapps.live.com/op/embed.aspx?src='
                    +
                    encodeURIComponent(url)
                );

            }


            if (
                ext === 'pdf'
            ) {

                return (
                    'https://docs.google.com/viewer?embedded=true&url='
                    +
                    encodeURIComponent(url)
                );

            }


            return (
                'https://docs.google.com/viewer?embedded=true&url='
                +
                encodeURIComponent(url)
            );

        }


        function openDoc(targetEl) {

            const explicitUrl =
                targetEl.getAttribute('href')
                ||
                targetEl.getAttribute('data-doc');


            if (!explicitUrl) {
                return;
            }


            const title =
                targetEl.getAttribute('data-title')
                ||
                targetEl.textContent.trim()
                ||
                'Document';


            const override =
                (
                    targetEl.getAttribute(
                        'data-viewer'
                    )
                    ||
                    ''
                ).toLowerCase();


            viewerWrap.classList.add(
                'loading'
            );


            titleEl.textContent =
                title;


            downloadBtn.href =
                explicitUrl;


            frame.src =
                getViewerURL(
                    explicitUrl,
                    override
                );


            modal.show();

        }


        triggers.forEach(
            function (el) {

                el.addEventListener(
                    'click',
                    function (event) {

                        const isAnchor =
                            el.tagName.toLowerCase()
                            === 'a';


                        const openInNew =
                            isAnchor &&
                            (
                                event.ctrlKey ||
                                event.metaKey ||
                                event.shiftKey ||
                                event.button === 1
                            );


                        if (!openInNew) {

                            event.preventDefault();

                            openDoc(el);

                        }

                    }
                );

            }
        );


        if (frame) {

            frame.addEventListener(
                'load',
                function () {

                    if (viewerWrap) {

                        viewerWrap.classList.remove(
                            'loading'
                        );

                    }

                }
            );

        }


        modalEl.addEventListener(
            'hidden.bs.modal',
            function () {

                frame.src = '';

                if (viewerWrap) {

                    viewerWrap.classList.add(
                        'loading'
                    );

                }

            }
        );

    })();

    </script>


</footer>