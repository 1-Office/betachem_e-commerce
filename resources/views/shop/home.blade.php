@extends('layouts.shop')



@section('title', 'Betachem Online Store')



@section('content')



<style>

    .shop-page {

        --shop-blue: #1745ca;

        --shop-blue-dark: #10369e;

        --shop-navy: #13264a;

        --shop-text: #29374d;

        --shop-muted: #78849a;

        --shop-border: #e5eaf2;

        color: var(--shop-text);

    }



    .shop-container {

        max-width: 1600px;

        margin: 0 auto;

    }



    /* HERO */



    .shop-hero {

        position: relative;

        min-height: 285px;

        padding: 42px 48px;

        overflow: hidden;

        border-radius: 15px;

        color: #fff;

        background: linear-gradient(112deg, #13264a 0%, #1745ca 58%, #248fe8 100%);

    }



    .shop-hero-content {

        position: relative;

        z-index: 2;

        max-width: 700px;

    }



    .shop-hero-eyebrow {

        display: inline-block;

        margin-bottom: 17px;

        color: #fff;

        font-size: 12px;

        font-weight: 800;

        letter-spacing: 1.4px;

    }



    .shop-hero-eyebrow i {

        margin-right: 6px;

        color: #8df0c0;

    }



    .shop-hero h1 {

        margin-bottom: 15px;

        font-size: clamp(2rem, 4vw, 3.1rem);

        font-weight: 800;

        letter-spacing: -1px;

    }



    .shop-hero p {

        max-width: 620px;

        margin-bottom: 22px;

        color: rgba(255,255,255,.86);

        font-size: 15px;

        line-height: 1.7;

    }



    .shop-hero-button {

        display: inline-flex;

        align-items: center;

        gap: 10px;

        padding: 12px 20px;

        border-radius: 7px;

        background: #fff;

        color: var(--shop-blue);

        font-size: 13px;

        font-weight: 800;

        text-decoration: none;

        transition: .2s ease;

    }



    .shop-hero-button:hover {

        background: #edf2ff;

        color: var(--shop-blue-dark);

        transform: translateY(-2px);

    }



    .shop-hero-decoration {

        position: absolute;

        top: 0;

        right: 0;

        width: 38%;

        height: 100%;

        pointer-events: none;

    }



    .shop-hero-circle {

        position: absolute;

        border: 1px solid rgba(255,255,255,.13);

        border-radius: 50%;

    }



    .circle-one {

        top: -170px;

        right: -45px;

        width: 390px;

        height: 390px;

    }



    .circle-two {

        right: 90px;

        bottom: -190px;

        width: 290px;

        height: 290px;

    }



    .shop-hero-icon {

        position: absolute;

        top: 50%;

        right: 19%;

        display: flex;

        align-items: center;

        justify-content: center;

        width: 105px;

        height: 105px;

        border: 1px solid rgba(255,255,255,.2);

        border-radius: 25px;

        background: rgba(255,255,255,.1);

        color: #fff;

        font-size: 52px;

        transform: translateY(-50%) rotate(-8deg);

    }



    .shop-hero-floating {

        position: absolute;

        right: 8%;

        bottom: 35px;

        padding: 10px 14px;

        border: 1px solid rgba(255,255,255,.13);

        border-radius: 8px;

        background: rgba(14,35,86,.8);

        color: #fff;

        font-size: 12px;

        font-weight: 700;

    }



    .shop-hero-floating i {

        margin-right: 6px;

        color: #8df0c0;

    }



    /* SEARCH */



    .shop-search {

        padding: 20px;

        margin: 22px 0 23px;

        border: 1px solid var(--shop-border);

        border-radius: 12px;

        background: #fff;

        box-shadow: 0 4px 14px rgba(25,45,80,.035);

    }



    .shop-search .form-control {

        height: 49px;

        border-color: #dce3ed;

        font-size: 14px;

    }



    .shop-search .form-control:focus {

        border-color: var(--shop-blue);

        box-shadow: 0 0 0 3px rgba(23,69,202,.09);

    }



    .shop-search .btn {

        min-height: 49px;

        padding: 0 22px;

        border-color: var(--shop-blue);

        background: var(--shop-blue);

        font-size: 13px;

        font-weight: 700;

    }



    .shop-search .btn:hover {

        border-color: var(--shop-blue-dark);

        background: var(--shop-blue-dark);

    }



    /* CATEGORIES */



    .category-strip {

        margin-bottom: 24px;

        overflow: hidden;

        border: 1px solid var(--shop-border);

        border-radius: 12px;

        background: #fff;

    }



    .category-strip-title {

        padding: 17px 20px;

        border-bottom: 1px solid var(--shop-border);

        color: var(--shop-navy);

        font-size: 15px;

        font-weight: 800;

    }



    .category-strip-title i {

        color: var(--shop-blue);

    }



    .category-list {

        display: flex;

        flex-wrap: wrap;

        padding: 8px;

    }



    .category-link {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        margin: 3px;

        padding: 10px 13px;

        border: 1px solid transparent;

        border-radius: 7px;

        color: #4c5a70;

        font-size: 12px;

        font-weight: 650;

        text-decoration: none;

        transition: .2s ease;

    }



    .category-link:hover,

    .category-link.active {

        border-color: #d7e1fa;

        background: #f1f5ff;

        color: var(--shop-blue);

    }



    /* SECTIONS */



    .shop-section {

        margin-bottom: 27px;

    }



    .shop-section-heading {

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 15px;

        margin-bottom: 18px;

    }



    .shop-section-heading h2 {

        margin: 0;

        color: var(--shop-navy);

        font-size: 21px;

        font-weight: 800;

    }



    .shop-section-heading .section-eyebrow {

        display: block;

        margin-bottom: 5px;

        color: var(--shop-blue);

        font-size: 10px;

        font-weight: 800;

        letter-spacing: 1px;

    }



    /* FEATURED PRODUCTS */



    .shop-featured {

        padding: 22px;

        border: 1px solid #e1e9ff;

        border-radius: 13px;

        background: #f7f9ff;

    }



    .featured-product {

        height: 100%;

        overflow: hidden;

        border: 1px solid var(--shop-border);

        border-radius: 10px;

        background: #fff;

        transition: .2s ease;

    }



    .featured-product:hover {

        transform: translateY(-3px);

        box-shadow: 0 8px 20px rgba(22,42,80,.08);

    }



    .featured-image {

        display: flex;

        align-items: center;

        justify-content: center;

        height: 155px;

        padding: 15px;

        border-bottom: 1px solid #f0f2f6;

        background: #fff;

    }



    .featured-image img {

        max-width: 100%;

        max-height: 100%;

        object-fit: contain;

    }



    .featured-body {

        padding: 13px;

    }



    .featured-category {

        margin-bottom: 6px;

        color: var(--shop-muted);

        font-size: 11px;

    }



    .featured-name {

        min-height: 40px;

        margin-bottom: 8px;

        font-size: 13px;

        font-weight: 750;

    }



    .featured-name a {

        color: var(--shop-navy);

        text-decoration: none;

    }



    .featured-name a:hover {

        color: var(--shop-blue);

    }



    .featured-price {

        color: var(--shop-blue);

        font-size: 16px;

        font-weight: 800;

    }



    /* FILTERS */



    .filter-card {

        overflow: hidden;

        border: 1px solid var(--shop-border);

        border-radius: 11px;

        background: #fff;

    }



    .filter-card-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 15px 18px;

        border-bottom: 1px solid var(--shop-border);

        color: var(--shop-navy);

        font-size: 14px;

        font-weight: 800;

    }



    .filter-card-body {

        padding: 18px;

    }



    .filter-title {

        display: block;

        margin-bottom: 8px;

        color: var(--shop-navy);

        font-size: 12px;

        font-weight: 800;

    }



    .filter-card .form-control,

    .filter-card .form-select {

        min-height: 40px;

        border-color: #dce3ed;

        border-radius: 7px;

        font-size: 12px;

    }



    .filter-check {

        display: inline-block;

        margin: 7px 13px 0 0;

        color: #526078;

        font-size: 12px;

    }



    .filter-check input {

        margin-right: 5px;

        accent-color: var(--shop-blue);

    }



    /* PRODUCT RESULTS */



    #shopProductResults {

        transition: opacity .2s ease;

    }



    .shop-results-toolbar {

        display: flex;

        justify-content: space-between;

        align-items: center;

        flex-wrap: wrap;

        gap: 10px;

        margin-bottom: 15px;

    }



    .shop-results-toolbar p {

        margin: 0;

        color: var(--shop-muted);

        font-size: 12px;

    }



    .shop-product-card {

        height: 100%;

        overflow: hidden;

        border: 1px solid var(--shop-border);

        border-radius: 10px;

        background: #fff;

        transition: .2s ease;

    }



    .shop-product-card:hover {

        transform: translateY(-3px);

        box-shadow: 0 9px 23px rgba(22,42,80,.09);

    }



    .shop-product-image {

        position: relative;

        display: flex;

        align-items: center;

        justify-content: center;

        height: 185px;

        padding: 16px;

        border-bottom: 1px solid #f0f2f6;

        background: #fff;

    }



    .shop-product-image img {

        max-width: 100%;

        max-height: 100%;

        object-fit: contain;

    }



    .shop-product-placeholder {

        color: #aab4c5;

        font-size: 3rem;

    }



    .shop-product-info {

        display: flex;

        flex-direction: column;

        min-height: 195px;

        padding: 15px;

    }



    .shop-product-category {

        color: var(--shop-muted);

        font-size: 11px;

        font-weight: 600;

    }



    .shop-product-name {

        min-height: 42px;

        margin: 7px 0;

        color: var(--shop-navy);

        font-size: 13px;

        font-weight: 750;

        line-height: 1.45;

    }



    .shop-product-price {

        margin-top: auto;

        color: var(--shop-blue);

        font-size: 17px;

        font-weight: 800;

    }



    .shop-stock {

        margin-top: 7px;

        font-size: 11px;

        font-weight: 700;

    }



    .shop-stock.available {

        color: #16834b;

    }



    .shop-stock.unavailable {

        color: #c0392b;

    }



    .shop-stock i {

        margin-right: 4px;

        font-size: 8px;

    }



    .product-actions {

        display: flex;

        gap: 7px;

        margin-top: 12px;

    }



    .product-actions a {

        display: inline-flex;

        flex: 1;

        align-items: center;

        justify-content: center;

        min-height: 39px;

        padding: 7px 5px;

        border: 1px solid #d7e1fa;

        border-radius: 6px;

        background: #f6f8ff;

        color: var(--shop-blue);

        font-size: 11px;

        font-weight: 800;

        text-align: center;

        text-decoration: none;

        transition: .2s ease;

    }



    .product-actions a:hover {

        border-color: var(--shop-blue);

        background: var(--shop-blue);

        color: #fff;

    }



    .shop-empty {

        padding: 55px 20px;

        border: 1px dashed #cbd4e2;

        border-radius: 12px;

        background: #fff;

        text-align: center;

    }



    .shop-empty i {

        margin-bottom: 15px;

        color: #aab4c5;

        font-size: 3rem;

    }



    .shop-empty h4 {

        color: var(--shop-navy);

        font-weight: 800;

    }



    .shop-loading {

        padding: 25px;

        color: var(--shop-blue);

        text-align: center;

    }



    @media (max-width: 767px) {

        .shop-hero {

            min-height: 260px;

            padding: 30px 22px;

        }



        .shop-hero-decoration {

            opacity: .35;

        }



        .shop-hero-icon {

            right: 5%;

            width: 75px;

            height: 75px;

            font-size: 37px;

        }



        .shop-hero-floating {

            display: none;

        }



        .shop-section-heading h2 {

            font-size: 18px;

        }



        .featured-image {

            height: 125px;

        }



        .shop-product-image {

            height: 145px;

        }



        .shop-product-info {

            min-height: 185px;

            padding: 11px;

        }



        .shop-product-name {

            font-size: 12px;

        }



        .shop-product-price {

            font-size: 15px;

        }

    }


    /* =========================================================
       BETACHEM SHOP UI REFRESH
       Keeps the existing Blade data, routes and AJAX behavior.
    ========================================================= */

    .shop-page {
        --shop-blue: #1554b8;
        --shop-blue-dark: #103d86;
        --shop-navy: #102746;
        --shop-text: #26384d;
        --shop-muted: #718096;
        --shop-border: #e3eaf2;
        --shop-surface: #ffffff;
        --shop-soft: #f4f7fb;
        background: #f6f8fb;
        padding-bottom: 24px;
    }

    .shop-container {
        max-width: 1540px;
        padding-left: clamp(14px, 2.2vw, 32px);
        padding-right: clamp(14px, 2.2vw, 32px);
    }

    .shop-hero {
        min-height: 300px;
        padding: clamp(28px, 4vw, 54px);
        border-radius: 18px;
        background:
            radial-gradient(circle at 82% 22%, rgba(77, 166, 235, .28), transparent 27%),
            linear-gradient(115deg, #0c203b 0%, #123e78 53%, #1768bd 100%);
        box-shadow: 0 18px 42px rgba(16, 39, 70, .14);
    }

    .shop-hero h1 {
        max-width: 690px;
        font-size: clamp(2rem, 4.2vw, 3.35rem);
        line-height: 1.08;
        letter-spacing: -1.4px;
    }

    .shop-hero p {
        font-size: 15px;
        max-width: 590px;
    }

    .shop-hero-eyebrow {
        padding: 8px 12px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 30px;
        background: rgba(255,255,255,.09);
        font-size: 10px;
        letter-spacing: 1.5px;
    }

    .shop-hero-button {
        min-height: 46px;
        border-radius: 8px;
        box-shadow: 0 7px 18px rgba(0,0,0,.12);
    }

    .shop-hero-icon {
        width: 122px;
        height: 122px;
        border-radius: 30px;
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(5px);
    }

    .shop-search {
        position: relative;
        z-index: 3;
        padding: 16px;
        margin-top: -2px;
        border: 1px solid #e5ebf3;
        border-radius: 13px;
        box-shadow: 0 8px 24px rgba(18, 42, 74, .055);
    }

    .shop-search .form-control {
        height: 52px;
        padding-left: 16px;
        border-radius: 8px;
        background: #fbfcfe;
    }

    .shop-search .btn {
        min-height: 52px;
        border-radius: 8px;
        background: var(--shop-blue);
    }

    .shop-section {
        margin-bottom: 30px;
    }

    .shop-section-heading {
        margin-bottom: 17px;
    }

    .shop-section-heading h2 {
        font-size: clamp(19px, 2vw, 24px);
        letter-spacing: -.45px;
    }

    .shop-section-heading .section-eyebrow {
        letter-spacing: 1.35px;
    }

    .shop-featured {
        padding: clamp(15px, 2vw, 24px);
        border: 1px solid #e2eaf5;
        border-radius: 15px;
        background: linear-gradient(180deg, #f0f5fc 0%, #f8faff 100%);
    }

    .featured-product,
    .shop-product-card {
        border: 1px solid #e5ebf2;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(16, 39, 70, .025);
    }

    .featured-product:hover,
    .shop-product-card:hover {
        border-color: #c8d8ee;
        transform: translateY(-4px);
        box-shadow: 0 13px 28px rgba(16, 39, 70, .09);
    }

    .featured-image,
    .shop-product-image {
        position: relative;
        background: linear-gradient(180deg, #fff 0%, #fafbfd 100%);
    }

    .featured-image {
        height: 165px;
        padding: 18px;
    }

    .shop-product-image {
        height: 195px;
        padding: 19px;
    }

    .featured-image img,
    .shop-product-image img {
        transition: transform .25s ease;
    }

    .featured-product:hover .featured-image img,
    .shop-product-card:hover .shop-product-image img {
        transform: scale(1.045);
    }

    .featured-body,
    .shop-product-info {
        padding: 16px;
    }

    .featured-name,
    .shop-product-name {
        color: var(--shop-navy);
        line-height: 1.45;
    }

    .featured-price,
    .shop-product-price {
        color: var(--shop-blue);
        letter-spacing: -.25px;
    }

    .filter-card {
        border: 1px solid #e2e9f2;
        border-radius: 13px;
        box-shadow: 0 5px 17px rgba(16, 39, 70, .035);
    }

    .filter-card-header {
        padding: 16px 19px;
        background: #fbfcfe;
    }

    .filter-card-body {
        padding: 19px;
    }

    .filter-card .form-control,
    .filter-card .form-select {
        min-height: 43px;
        border-radius: 8px;
        background-color: #fff;
    }

    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #8eadd8;
        box-shadow: 0 0 0 3px rgba(21, 84, 184, .09);
    }

    .filter-card .btn {
        min-height: 42px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .shop-results-toolbar {
        padding: 12px 14px;
        border: 1px solid var(--shop-border);
        border-radius: 9px;
        background: #fff;
    }

    .shop-product-info {
        min-height: 205px;
    }

    .shop-stock {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .product-actions a {
        min-height: 40px;
        border-radius: 7px;
    }

    .shop-empty {
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(16, 39, 70, .035);
    }

    #shopProductResults {
        min-height: 160px;
    }

    #shopProductResults .pagination {
        margin-top: 22px;
        gap: 4px;
    }

    #shopProductResults .page-link {
        border-radius: 7px !important;
        color: var(--shop-blue);
    }

    #shopProductResults .page-item.active .page-link {
        border-color: var(--shop-blue);
        background: var(--shop-blue);
        color: #fff;
    }

    @media (min-width: 1200px) {
        .shop-product-image {
            height: 205px;
        }
    }

    @media (max-width: 767px) {
        .shop-container {
            padding-left: 12px;
            padding-right: 12px;
        }

        .shop-hero {
            min-height: 265px;
            padding: 27px 22px;
            border-radius: 13px;
        }

        .shop-hero h1 {
            max-width: 470px;
            font-size: clamp(1.85rem, 8vw, 2.45rem);
        }

        .shop-hero p {
            max-width: 440px;
            font-size: 13px;
        }

        .shop-hero-decoration {
            width: 48%;
            opacity: .32;
        }

        .shop-hero-icon {
            right: 2%;
            width: 70px;
            height: 70px;
            border-radius: 18px;
            font-size: 32px;
        }

        .shop-search {
            padding: 12px;
            margin-top: 0;
        }

        .shop-search .form-control,
        .shop-search .btn {
            min-height: 47px;
            height: 47px;
        }

        .shop-section-heading {
            align-items: flex-start;
        }

        .shop-featured {
            padding: 13px;
        }

        .featured-image {
            height: 125px;
        }

        .shop-product-image {
            height: 145px;
            padding: 12px;
        }

        .shop-product-info {
            min-height: 190px;
            padding: 11px;
        }

        .shop-product-name {
            min-height: 42px;
            font-size: 12px;
        }

        .shop-product-price {
            font-size: 15px;
        }

        .product-actions {
            flex-direction: column;
        }

        .product-actions a {
            width: 100%;
        }

        .filter-card-body {
            padding: 14px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .shop-page *,
        .shop-page *::before,
        .shop-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }

</style>



<div class="shop-page">

    <div class="container-fluid shop-container py-4">



        {{-- HERO --}}

        <section class="shop-hero mb-4">

            <div class="shop-hero-content">



                <span class="shop-hero-eyebrow">

                    <i class="fa fa-check-circle"></i>

                    BETACHEM ONLINE STORE

                </span>



                <h1>Everything your business needs.</h1>



                <p>

                    Discover our range of chemical products and

                    industrial solutions. Find the products you need

                    in one convenient online shopping destination.

                </p>



                <a href="#shop-products" class="shop-hero-button">

                    Explore products

                    <i class="fa fa-arrow-right"></i>

                </a>



            </div>



            <div class="shop-hero-decoration" aria-hidden="true">

                <div class="shop-hero-circle circle-one"></div>

                <div class="shop-hero-circle circle-two"></div>



                <div class="shop-hero-icon">

                    <i class="fa fa-flask"></i>

                </div>



                <div class="shop-hero-floating">

                    <i class="fa fa-check-circle"></i>

                    Quality solutions

                </div>

            </div>

        </section>



        {{-- TOP SEARCH --}}

        <div class="shop-search">

            <form action="{{ url('/shop') }}" method="GET" id="shopTopSearchForm">



                <div class="row g-2">



                    <div class="col-md">

                        <input

                            type="text"

                            name="search"

                            id="shopTopSearch"

                            class="form-control"

                            value="{{ $search }}"

                            placeholder="Search products by name, SKU or description..."

                            aria-label="Search products">

                    </div>



                    <div class="col-md-auto">

                        <button type="submit" class="btn btn-primary w-100">

                            <i class="fa fa-search me-2"></i>

                            Search Products

                        </button>

                    </div>



                </div>



            </form>

        </div>



        {{-- CATEGORIES --}}

       {{-- <section class="category-strip">



            <div class="category-strip-title">

                <i class="fa fa-th-large me-2"></i>

                Shop by Category

            </div>



            <div class="category-list">



                <a

                    href="{{ url('/shop') }}"

                    class="category-link {{ $categorySlug === '' ? 'active' : '' }}"

                    data-category="">

                    All Categories

                </a>



                @foreach($categories as $category)

                    <a

                        href="{{ url('/shop') }}?category={{ urlencode($category->slug) }}"

                        class="category-link {{ $categorySlug === $category->slug ? 'active' : '' }}"

                        data-category="{{ $category->slug }}">

                        {{ $category->name }}

                        <span class="text-muted">

                            ({{ $category->product_count }})

                        </span>

                    </a>

                @endforeach



            </div>



        </section> --}}



        {{-- FEATURED PRODUCTS --}}

        @if($featuredProducts->isNotEmpty())



            <section class="shop-section shop-featured">



                <div class="shop-section-heading">



                    <div>

                        <span class="section-eyebrow">OUR SELECTION</span>



                        <h2>

                            <i class="fa fa-star text-warning me-2"></i>

                            Featured Products

                        </h2>

                    </div>



                    <a

                        href="{{ url('/shop') }}?featured=1"

                        class="btn btn-outline-primary btn-sm">

                        View All Featured

                        <i class="fa fa-arrow-right ms-1"></i>

                    </a>



                </div>



                <div class="row g-3">



                    @foreach($featuredProducts as $product)



                        <div class="col-6 col-md-3">



                            <article class="featured-product">



                                <div class="featured-image">



                                    <a

                                        href="{{ url('/shop/products/' . $product->slug) }}"

                                        class="w-100 h-100 d-flex align-items-center justify-content-center">



                                        @if($product->main_image)

                                            <img

                                                src="{{ asset('uploads/products/' . $product->main_image) }}"

                                                alt="{{ $product->name }}"

                                                loading="lazy">

                                        @else

                                            <i class="fa fa-flask shop-product-placeholder"></i>

                                        @endif



                                    </a>



                                </div>



                                <div class="featured-body">



                                    <div class="featured-category">

                                        {{ $product->category?->name ?? 'Product' }}

                                    </div>



                                    <div class="featured-name">

                                        <a href="{{ url('/shop/products/' . $product->slug) }}">

                                            {{ $product->name }}

                                        </a>

                                    </div>



                                    <div class="featured-price">

                                        R{{ number_format((float) $product->price, 2) }}

                                    </div>



                                </div>



                            </article>



                        </div>



                    @endforeach



                </div>



            </section>



        @endif



        {{-- ALL PRODUCTS --}}

        <section class="shop-section" id="shop-products">



            <div class="shop-section-heading">



                <div>

                    <span class="section-eyebrow">EXPLORE OUR RANGE</span>



                    <h2 id="shopHeading">

                        {{ $selectedCategory ? $selectedCategory->name : 'All Products' }}

                    </h2>

                </div>



                <span class="badge bg-light text-dark border px-3 py-2" id="shopProductCount">

                    {{ $products->total() }} Products

                </span>



            </div>



            {{-- FILTERS ABOVE PRODUCTS --}}

            <div class="filter-card mb-4">



                <div class="filter-card-header">



                    <span>

                        <i class="fa fa-sliders me-2 text-primary"></i>

                        Filter Products

                    </span>



                    <span class="badge bg-primary" id="activeFilterBadge">

                        {{ $activeFilters }} Active

                    </span>



                </div>



                <div class="filter-card-body">



                    <form action="{{ url('/shop') }}" method="GET" id="shopFilterForm">



                        <div class="row g-3 align-items-end">



                            <div class="col-12 col-md-6 col-lg-3">



                                <label class="filter-title" for="filter-search">

                                    Search Products

                                </label>



                                <input

                                    type="text"

                                    name="search"

                                    id="filter-search"

                                    class="form-control"

                                    value="{{ $search }}"

                                    placeholder="Product name or SKU">



                            </div>



                            <div class="col-12 col-md-6 col-lg-3">



                                <label class="filter-title" for="filter-category">

                                    Category

                                </label>



                                <select name="category" id="filter-category" class="form-select">



                                    <option value="">All Categories</option>



                                    @foreach($categories as $category)

                                        <option

                                            value="{{ $category->slug }}"

                                            {{ $categorySlug === $category->slug ? 'selected' : '' }}>

                                            {{ $category->name }}

                                        </option>

                                    @endforeach



                                </select>



                            </div>



                            <div class="col-12 col-md-6 col-lg-2">



                                <label class="filter-title" for="filter-price">

                                    Price Range

                                </label>



                                <select name="price_range" id="filter-price" class="form-select">



                                    @foreach($priceRanges as $key => $range)

                                        <option

                                            value="{{ $key }}"

                                            {{ $priceRange === $key ? 'selected' : '' }}>

                                            {{ $range['label'] }}

                                        </option>

                                    @endforeach



                                </select>



                            </div>



                            <div class="col-12 col-md-6 col-lg-2">



                                <label class="filter-title" for="filter-sort">

                                    Sort Products

                                </label>



                                <select name="sort" id="filter-sort" class="form-select">



                                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>

                                        Newest First

                                    </option>



                                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>

                                        Featured First

                                    </option>



                                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>

                                        Price: Low to High

                                    </option>



                                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>

                                        Price: High to Low

                                    </option>



                                    <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>

                                        Name: A to Z

                                    </option>



                                    <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>

                                        Name: Z to A

                                    </option>



                                </select>



                            </div>



                            <div class="col-12 col-lg-2">



                                <label class="filter-title d-block">

                                    Availability

                                </label>



                                <label class="filter-check">

                                    <input

                                        type="checkbox"

                                        name="in_stock"

                                        value="1"

                                        {{ $inStock ? 'checked' : '' }}>

                                    In Stock

                                </label>



                                <label class="filter-check">

                                    <input

                                        type="checkbox"

                                        name="featured"

                                        value="1"

                                        {{ $featured ? 'checked' : '' }}>

                                    Featured

                                </label>



                            </div>



                            <div class="col-12 d-flex align-items-center gap-3">



                                <a

                                    href="{{ url('/shop') }}"

                                    class="btn btn-outline-secondary px-4"

                                    id="resetShopFilters">

                                    <i class="fa fa-refresh me-2"></i>

                                    Reset Filters

                                </a>



                                <span

                                    class="text-muted small"

                                    id="filterLoading"

                                    style="display:none;">

                                    <i class="fa fa-spinner fa-spin me-1"></i>

                                    Updating products...

                                </span>



                            </div>



                        </div>



                    </form>



                </div>



            </div>



            {{-- AJAX PRODUCT RESULTS --}}

            <div id="shopProductResults" aria-live="polite">

                @include('shop.partials.product-results', ['products' => $products])

            </div>



        </section>



    </div>

</div>



{{-- AJAX FILTERING AND PAGINATION --}}

<script>

document.addEventListener('DOMContentLoaded', function () {



    const filterForm = document.getElementById('shopFilterForm');

    const topSearchForm = document.getElementById('shopTopSearchForm');

    const topSearchInput = document.getElementById('shopTopSearch');

    const resultsContainer = document.getElementById('shopProductResults');

    const loadingIndicator = document.getElementById('filterLoading');

    const resetButton = document.getElementById('resetShopFilters');

    const activeFilterBadge = document.getElementById('activeFilterBadge');

    const productCount = document.getElementById('shopProductCount');

    const categoryLinks = document.querySelectorAll('.category-link');



    if (!filterForm || !resultsContainer) {

        return;

    }



    let activeRequest = null;

    let searchTimer = null;

    let currentRequestNumber = 0;



    /**

     * Synchronise search fields.

     */

    function syncSearchFields(value) {

        filterForm.querySelector('[name="search"]').value = value;



        if (topSearchInput) {

            topSearchInput.value = value;

        }

    }



    /**

     * Update the active category styling.

     */

    function updateCategoryLinks(category) {



        categoryLinks.forEach(function (link) {



            const linkCategory = link.dataset.category || '';



            link.classList.toggle(

                'active',

                linkCategory === category

            );



        });

    }



    /**

     * Update filter badge.

     */

    function updateFilterBadge(params) {



        let count = 0;



        ['category', 'search', 'price_range', 'in_stock', 'featured'].forEach(function (key) {



            if (params.get(key)) {

                count++;

            }



        });



        activeFilterBadge.textContent = count + ' Active';

    }



    /**

     * Build query parameters from the filter form.

     */

    function getFilterParams() {



        const params = new URLSearchParams(new FormData(filterForm));



        for (const [key, value] of [...params.entries()]) {

            if (value === '') {

                params.delete(key);

            }

        }



        return params;

    }



    /**

     * Load products without refreshing the page.

     */

    async function loadShopProducts(options = {}) {



        const {

            url = null,

            updateHistory = true,

            scrollToProducts = false

        } = options;



        const requestNumber = ++currentRequestNumber;



        if (activeRequest) {

            activeRequest.abort();

        }



        activeRequest = new AbortController();



        let finalUrl;



        if (url) {

            finalUrl = new URL(url, window.location.origin);

        } else {

            const params = getFilterParams();

            finalUrl = new URL(filterForm.action, window.location.origin);



            params.forEach(function (value, key) {

                finalUrl.searchParams.set(key, value);

            });

        }



        const requestUrl = finalUrl.toString();



        loadingIndicator.style.display = 'inline-block';

        resultsContainer.style.opacity = '0.45';



        try {



            const response = await fetch(requestUrl, {

                method: 'GET',

                headers: {

                    'X-Requested-With': 'XMLHttpRequest',

                    'Accept': 'application/json'

                },

                signal: activeRequest.signal

            });



            if (!response.ok) {

                throw new Error('Unable to load products. HTTP ' + response.status);

            }



            const data = await response.json();



            if (requestNumber !== currentRequestNumber) {

                return;

            }



            resultsContainer.innerHTML = data.html;



            if (productCount) {

                productCount.textContent = data.total + ' Products';

            }



            if (updateHistory) {

                window.history.pushState(

                    {},

                    '',

                    requestUrl

                );

            }



            updateFilterBadge(finalUrl.searchParams);



            updateCategoryLinks(

                finalUrl.searchParams.get('category') || ''

            );



            if (scrollToProducts) {

                document.getElementById('shop-products').scrollIntoView({

                    behavior: 'smooth',

                    block: 'start'

                });

            }



        } catch (error) {



            if (error.name !== 'AbortError') {

                console.error(error);

                alert('We could not update the products. Please try again.');

            }



        } finally {



            if (requestNumber === currentRequestNumber) {

                loadingIndicator.style.display = 'none';

                resultsContainer.style.opacity = '1';

                activeRequest = null;

            }



        }

    }



    /**

     * Automatically apply dropdown and checkbox filters.

     */

    filterForm.addEventListener('change', function (event) {



        if (

            event.target.matches('select') ||

            event.target.matches('input[type="checkbox"]')

        ) {

            if (event.target.name === 'search' && topSearchInput) {

                topSearchInput.value = event.target.value;

            }



            loadShopProducts();

        }



    });



    /**

     * Search while typing, with a short delay.

     */

    const filterSearchInput = filterForm.querySelector('[name="search"]');



    if (filterSearchInput) {



        filterSearchInput.addEventListener('input', function () {



            clearTimeout(searchTimer);



            syncSearchFields(this.value);



            searchTimer = setTimeout(function () {

                loadShopProducts();

            }, 450);



        });



    }



    /**

     * Top search box also updates products automatically.

     */

    if (topSearchInput) {



        topSearchInput.addEventListener('input', function () {



            clearTimeout(searchTimer);



            syncSearchFields(this.value);



            searchTimer = setTimeout(function () {

                loadShopProducts();

            }, 450);



        });



    }



    /**

     * Prevent normal form submission.

     */

    filterForm.addEventListener('submit', function (event) {

        event.preventDefault();



        clearTimeout(searchTimer);



        loadShopProducts({

            scrollToProducts: true

        });

    });



    if (topSearchForm) {



        topSearchForm.addEventListener('submit', function (event) {



            event.preventDefault();



            clearTimeout(searchTimer);



            syncSearchFields(topSearchInput.value);



            loadShopProducts({

                scrollToProducts: true

            });



        });



    }



    /**

     * Category links use AJAX instead of normal navigation.

     */

    categoryLinks.forEach(function (link) {



        link.addEventListener('click', function (event) {



            event.preventDefault();



            const category = this.dataset.category || '';



            filterForm.querySelector('[name="category"]').value = category;



            loadShopProducts({

                scrollToProducts: true

            });



        });



    });



    /**

     * AJAX pagination.

     */

    resultsContainer.addEventListener('click', function (event) {



        const link = event.target.closest('.pagination a');



        if (!link) {

            return;

        }



        event.preventDefault();



        loadShopProducts({

            url: link.href,

            scrollToProducts: true

        });



    });



    /**

     * Reset filters without reloading.

     */

    window.resetShopFilters = function () {



        clearTimeout(searchTimer);



        filterForm.reset();



        if (topSearchInput) {

            topSearchInput.value = '';

        }



        loadShopProducts({

            scrollToProducts: true

        });



    };



    if (resetButton) {



        resetButton.addEventListener('click', function (event) {



            event.preventDefault();



            window.resetShopFilters();



        });



    }



    /**

     * Browser Back and Forward buttons.

     */

    window.addEventListener('popstate', function () {



        clearTimeout(searchTimer);



        const currentUrl = new URL(window.location.href);



        filterForm.querySelectorAll('[name]').forEach(function (field) {



            if (field.type === 'checkbox') {

                field.checked =

                    currentUrl.searchParams.get(field.name) === field.value;

            } else {

                field.value =

                    currentUrl.searchParams.get(field.name) || '';

            }



        });



        if (topSearchInput) {

            topSearchInput.value =

                currentUrl.searchParams.get('search') || '';

        }



        loadShopProducts({

            url: currentUrl.toString(),

            updateHistory: false

        });



    });



});

</script>



@endsection
