@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <form method="get" action="{{ route('api.report.write') }}">
                    <div class="card-body">

                        <div class="input-group mb-3">
                            <label class="col-md-4 col-form-label text-md-right" for="created_On1">Creanted On and After</label>
                            <input type="text" class="form-control" name="createdOn1">
                            
                        </div>

                        <div class="input-group mb-3">
                            <label class="col-md-4 col-form-label text-md-right" for="created_On1">Creanted On and Before</label>
                            <input type="text" class="form-control" name="createdOn2">
                            
                        </div>

                        <div class="input-group mb-3">
                            <button type="submit" class="form-control" id="submit">submit</button>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection