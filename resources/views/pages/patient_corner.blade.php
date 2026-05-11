@extends('layout.frontend')

@section('content')

{{-- ================= PAGE HEADER ================= --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title ?? 'Patient Data' }}</h1>

                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>{{ $page->title ?? 'Patient Data' }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>


<section class="ntpcsection">
    <div class="container">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================= TABS NAVIGATION ================= --}}
        <ul class="nav nav-tabs mb-4" id="patientTabs" role="tablist">

            <li class="nav-item">
                <button class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#opd"
                        type="button">
                    OPD Patient Data
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#cured"
                        type="button">
                    Cured Patient
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#file"
                        type="button">
                    File Number
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#idCard"
                        type="button">
                    ID Card
                </button>
            </li>

        </ul>


        {{-- ================= TAB CONTENT ================= --}}
        <div class="tab-content">

            {{-- ========================================= --}}
            {{-- 1️⃣ OPD PATIENT TAB --}}
            {{-- ========================================= --}}
            <div class="tab-pane fade show active" id="opd">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif


                {{-- Excel Upload --}}
                {{-- ================= OPD EXCEL SECTION ================= --}}
                <div class="card mb-4 p-3" style="background:#f8f9fa;">
                    <h4>OPD Excel Upload</h4>

                    {{-- Upload Form --}}
                    <form action="{{ route('upload.opd') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success">
                                    Upload Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ================= DOWNLOAD SECTION ================= --}}
                <div class="card p-4">
                    <h4>Download OPD Record by LTBI (UHID)</h4>

                    {{-- Download Form --}}
                    <form action="{{ route('download.opd') }}" method="GET">
                        <div class="row align-items-center">

                            <div class="col-md-6">
                                <input type="text"
                                    name="uhid_no"
                                    class="form-control"
                                    value="{{ old('uhid_no') }}"
                                    placeholder="Enter LTBI Number (Example: LTBI00035)"
                                    maxlength="64"
                                    pattern="[Ll][Tt][Bb][Ii]\d{1,12}"
                                    title="Format: LTBI followed by digits (e.g. LTBI00035)"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Download OPD Record
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="card p-4 mt-3">
                    <h4>Download OPD Record by Last 4 UHID + File No.</h4>
                    <form action="{{ route('download.opd.last4_file') }}" method="GET">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <input type="text"
                                       name="uhid_last4"
                                       class="form-control"
                                       maxlength="4"
                                       pattern="\d{4}"
                                       placeholder="Enter Last 4 UHID digits"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <input type="text"
                                       name="file_no"
                                       class="form-control"
                                       placeholder="Enter File Number"
                                       required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Download OPD Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>


                {{-- Manual Entry Form --}}
                <form action="{{ route('patients.store') }}" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>UHID No.</label>
                            <input type="text" name="uhid_no" class="form-control" value="{{ old('uhid_no') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>File No.</label>
                            <input type="text" name="file_no" class="form-control" value="{{ old('file_no') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Adhaar No.</label>
                            <input type="text" name="adhaar_no" class="form-control"
                                value="{{ old('adhaar_no') }}" maxlength="14" pattern="\d{4}[\s\-]?\d{4}[\s\-]?\d{4}|\d{12}"
                                title="Aadhaar number must be exactly 12 digits (optional spaces or hyphens between groups)" required inputmode="numeric" autocomplete="off">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required minlength="2" maxlength="255" autocomplete="name">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Age</label>
                            <input type="number" name="age" class="form-control" value="{{ old('age') }}" min="0" max="120" step="1" inputmode="numeric">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label>Sex</label>
                            <select name="sex" class="form-control">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Visit / Follow up</label>
                            <input type="text" name="visit_follow_up" class="form-control">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>

                        @php
                            $fields = [
                                'diagnosis','investigation','medicines','h_o_tb_other_investigations',
                                'tb_gold','montoux_test','cbc_esr','xray_cect_hrct','gene_xpert',
                                'usg_wa_ct_scan','cd4_cd8','ige','vit_d','lft','rft','il2',
                                'contact_details','ltbi_qs_10','ltbi_qs_09','refer'
                            ];
                        @endphp

                        @foreach($fields as $field)
                        <div class="col-md-6 mb-3">
                            <label>{{ ucwords(str_replace('_',' ',$field)) }}</label>
                            <input type="text" name="{{ $field }}" class="form-control">
                        </div>
                        @endforeach

                        <div class="col-md-12 mb-3">
                            <button type="submit" class="btn btn-dark">
                                Submit Patient Data
                            </button>
                        </div>

                    </div>
                </form>

            </div>

            {{-- 2️⃣ CURE PATIENT DATA TAB --}}
            {{-- ========================================= --}}
            <div class="tab-pane fade" id="cured">

                @if(session('cure_success'))

                    <div class="alert alert-success">
                        {{ session('cure_success') }}
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            var triggerEl = document.querySelector('[data-bs-target="#cured"]');
                            var tab = new bootstrap.Tab(triggerEl);
                            tab.show();
                        });
                    </script>

                @endif

                <div class="card p-4 mb-4" style="background:#f8f9fa;">
                    <h4>Cure Patient Document Upload</h4>

                    <form action="{{ route('cure.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            {{-- LTBI Number --}}
                            <div class="col-md-4 mb-3">
                                <label>LTBI Number</label>
                                <input type="text" name="ltbi_no" class="form-control"
                                    value="{{ old('ltbi_no') }}"
                                    placeholder="Example: 00035"
                                    inputmode="numeric"
                                    maxlength="12"
                                    pattern="\d{1,12}"
                                    title="Numeric only, up to 12 digits"
                                    required>
                            </div>

                            {{-- Type Selection --}}
                            <div class="col-md-4 mb-3">
                                <label>Select Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="cc" @selected(old('type') === 'cc')>CC Number</option>
                                    <option value="tr" @selected(old('type') === 'tr')>TR Number</option>
                                </select>
                            </div>

                            {{-- CC Number --}}
                            <div class="col-md-4 mb-3">
                                <label>CC Number (If Selected)</label>
                                <input type="text" name="cc_no" class="form-control"
                                    value="{{ old('cc_no') }}"
                                    placeholder="Example: 00049"
                                    inputmode="numeric"
                                    maxlength="12"
                                    pattern="\d{1,12}"
                                    title="Numeric only">
                            </div>

                            {{-- TR Number --}}
                            <div class="col-md-4 mb-3">
                                <label>TR Number (If Selected)</label>
                                <input type="text" name="tr_no" class="form-control"
                                    value="{{ old('tr_no') }}"
                                    placeholder="Example: 00047"
                                    inputmode="numeric"
                                    maxlength="12"
                                    pattern="\d{1,12}"
                                    title="Numeric only">
                            </div>

                            {{-- File Upload --}}
                            <div class="col-md-6 mb-3">
                                <label>Upload File (PDF / JPEG)</label>
                                <input type="file" name="file" class="form-control"
                                    accept=".pdf,.jpg,.jpeg" required>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success">
                                    Upload Cure Patient Document
                                </button>
                            </div>

                        </div>
                    </form>
                </div>


                {{-- ================= DOWNLOAD SECTION ================= --}}
                <div class="card p-4">
                    <h4>Download Cure Patient Document</h4>

                    <form action="{{ route('cure.download') }}" method="GET">
                        <div class="row align-items-center">

                            <div class="col-md-6">
                                <input type="text"
                                    name="access_code"
                                    class="form-control"
                                    value="{{ old('access_code') }}"
                                    placeholder="Enter Access Code (Example: 0035049)"
                                    inputmode="numeric"
                                    maxlength="7"
                                    pattern="\d{7}"
                                    title="Exactly 7 digits"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Download File
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>


            {{-- ========================================= --}}
            {{-- 3️⃣ FILE NUMBER TAB (RESEARCH PATIENT) --}}
            {{-- ========================================= --}}
            <div class="tab-pane fade" id="file">

                @if(session('research_success'))

                    <div class="alert alert-success">
                        {{ session('research_success') }}
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            var triggerEl = document.querySelector('[data-bs-target="#file"]');
                            var tab = new bootstrap.Tab(triggerEl);
                            tab.show();
                        });
                    </script>

                @endif

                {{-- Upload Section --}}
                <div class="card p-4 mb-4" style="background:#f8f9fa;">
                    <h4>Research Patient File Upload</h4>

                    <form action="{{ route('research.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label>LTBIRS Number</label>
                                <input type="text" name="ltbirs_no"
                                    class="form-control"
                                    value="{{ old('ltbirs_no') }}"
                                    placeholder="Example: LTBIRS0001"
                                    maxlength="10"
                                    pattern="[Ll][Tt][Bb][Ii][Rr][Ss]\d{4}"
                                    title="LTBIRS + 4 digits (e.g. LTBIRS0001)"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Upload File (PDF / JPEG)</label>
                                <input type="file" name="file"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg"
                                    required>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success">
                                    Upload File
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                {{-- Download Section --}}
                <div class="card p-4">
                    <h4>Download Research Patient File</h4>

                    <form action="{{ route('research.download') }}" method="GET">
                        <div class="row align-items-center">

                            <div class="col-md-6">
                                <input type="text"
                                    name="ltbirs_no"
                                    class="form-control"
                                    value="{{ old('ltbirs_no') }}"
                                    placeholder="Enter LTBIRS Number"
                                    maxlength="10"
                                    pattern="[Ll][Tt][Bb][Ii][Rr][Ss]\d{4}"
                                    title="LTBIRS + 4 digits (e.g. LTBIRS0001)"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Download File
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>


            {{-- ========================================= --}}
            {{-- 4️⃣ ID CARD TAB --}}
            {{-- ========================================= --}}
            <div class="tab-pane fade" id="idCard">

                @if(session('id_success'))

                    <div class="alert alert-success">
                        {{ session('id_success') }}
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            var triggerEl = document.querySelector('[data-bs-target="#idCard"]');
                            var tab = new bootstrap.Tab(triggerEl);
                            tab.show();
                        });
                    </script>

                @endif

                {{-- Upload Section --}}
                <div class="card p-4 mb-4" style="background:#f8f9fa;">
                    <h4>ID Card Upload</h4>

                    <form action="{{ route('idcard.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label>ID Number</label>
                                <input type="text"
                                    name="id_number"
                                    class="form-control"
                                    value="{{ old('id_number') }}"
                                    placeholder="Example: ID0001"
                                    maxlength="6"
                                    pattern="[Ii][Dd]\d{4}"
                                    title="ID + 4 digits (e.g. ID0001)"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Upload File (PDF / JPEG)</label>
                                <input type="file"
                                    name="file"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg"
                                    required>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success">
                                    Upload ID Card
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                {{-- Download Section --}}
                <div class="card p-4">
                    <h4>Download ID Card</h4>

                    <form action="{{ route('idcard.download') }}" method="GET">
                        <div class="row align-items-center">

                            <div class="col-md-6">
                                <input type="text"
                                    name="id_number"
                                    class="form-control"
                                    value="{{ old('id_number') }}"
                                    placeholder="Enter ID Number (Example: ID0001)"
                                    maxlength="6"
                                    pattern="[Ii][Dd]\d{4}"
                                    title="ID + 4 digits (e.g. ID0001)"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Download ID Card
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</section>

@endsection