@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <form method="POST" action="{{ route('api.report.import') }}" enctype="multipart/form-data">
                    <div class="card-body">
                        @if (session('file'))
                            <div class="alert alert-success" role="alert">
                                {{ session('file') }}
                            </div>
                        @endif

                        <div class="input-group mb-3">
                            <input type="file" class="form-control" name="file" id="inputGroupFile02" accept=".xls,.xlsx,.xlsb,.xlsm">
                            <label class="input-group-text" for="inputGroupFile02">Upload</label>
                        </div>

                        <div class="input-group mb-3">
                            <button type="submit" class="form-control" id="submit">Upload</button>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
