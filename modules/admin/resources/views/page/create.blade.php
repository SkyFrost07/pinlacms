@extends('admin::layouts.manage')

@section('title', trans('admin::view.man_pages'))

@section('content')

{!! showMessage() !!}

<?php 
use Admin\Facades\AdConst;

$langs = getLangs(); 
?>

{!! Form::open(['method' => 'post', 'route' => 'admin::page.store']) !!}

<div class="row">
    <div class="col-sm-9">

        @include('admin::parts.lang_tabs')

        <div class="tab-content">
            @foreach($langs as $lang)
            <?php $code = $lang->code; ?>
            <div class="tab-pane fade in {{ localeActive($code) }}" id="tab-{{$lang->code}}">

                <div class="form-group">
                    <label>{{trans('admin::view.name')}} (*)</label>
                    {!! Form::text($code.'[title]', old($code.'.title'), ['class' => 'form-control', 'placeholder' => trans('admin::view.title')]) !!}
                    {!! errorField($code.'.title') !!}
                </div>

                <div class="form-group">
                    <label>{{trans('admin::view.slug')}}</label>
                    {!! Form::text($code.'[slug]', old($code.'.slug'), ['class' => 'form-control', 'placeholder' => trans('admin::view.slug')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('admin::view.content')}}</label>
                    {!! Form::textarea($code.'[content]', old($code.'.content'), ['class' => 'form-control editor_content', 'rows' => 15, 'placeholder' => trans('admin::view.content')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('admin::view.excerpt')}}</label>
                    {!! Form::textarea($code.'[excerpt]', old($code.'.excerpt'), ['class' => 'form-control editor_excerpt', 'rows' => 5, 'placeholder' => trans('admin::view.excerpt')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('admin::view.meta_keyword')}}</label>
                    {!! Form::text($code.'[meta_keyword]', old($code.'.meta_keyword'), ['class' => 'form-control', 'placeholder' => trans('admin::view.meta_keyword')]) !!}
                </div>

                <div class="form-group">
                    <label>{{trans('admin::view.meta_desc')}}</label>
                    {!! Form::textarea($code.'[meta_desc]', old($code.'.meta_desc'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => trans('admin::view.meta_desc')]) !!}
                </div>

            </div>
            @endforeach
        </div>

    </div>

    <div class="col-sm-3">
        
        <div class="form-group">
            <label>{{trans('admin::view.status')}}</label>
            {!! Form::select('status', AdView::getStatusLabel(), old('status'), ['class' => 'form-control']) !!}
        </div>
        
        <div class="form-group">
            <label>{{trans('admin::view.template')}}</label>
            {!! Form::select('template', $templates, old('template'), ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            <label>{{trans('admin::view.comment_status')}}</label>
            {!! Form::select('comment_status', AdView::commentStatusLabel(), old('comment_status'), ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            <label>{{trans('admin::view.views')}}</label>
            {!! Form::number('views', old('views'), ['class' => 'form-control']) !!}
        </div>
        
        
        <div class="form-group">
            <label>{{trans('admin::view.created_at')}}</label>
            <div class="time_group">
                <div class="t_field">
                    <span>{{trans('admin::view.day')}}</span>
                    <select name="time[day]" class="form-control">
                        {!! rangeOptions(1, 31, date('d')) !!}
                    </select>
                </div>
                <div class="t_field">
                    <span>{{trans('admin::view.month')}}</span>
                    <select name="time[month]" class="form-control">
                        {!! rangeOptions(1, 12, date('m')) !!}
                    </select>
                </div>
                <div class="t_field">
                    <span>{{trans('admin::view.year')}}</span>
                    <select name="time[year]" class="form-control">
                        {!! rangeOptions(2010, 2030, date('Y')) !!}
                    </select>
                </div>
            </div>
        </div>
        

        <div class="form-group thumb_box" >
            <label>{{trans('admin::view.thumbnail')}}</label>
            <div class="thumb_group">
            </div>
            <div><button type="button" class="btn btn-default btn-files-modal" data-href="{{route('admin::file.dialog')}}">{{trans('admin::view.add_image')}}</button></div>
        </div>
        
        <div class="form-group">
            <a href="{{route('admin::page.index', ['status' => AdConst::STT_PUBLISH])}}" class="btn btn-warning"><i class="fa fa-long-arrow-left"></i> {{trans('admin::view.back')}}</a>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{trans('admin::view.create')}}</button>
        </div>

    </div>
</div>

{!! Form::close() !!}

@stop

@section('foot')

@include('admin::parts.tinymce-script')

@include('admin::file.manager')

@stop

