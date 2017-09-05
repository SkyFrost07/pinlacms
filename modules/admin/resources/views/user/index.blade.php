@extends('layouts.manage')

@section('title', trans('manage.man_users'))

@section('page_title', trans('manage.man_users'))

<?php
use App\User;
?>

@section('options')
<li class="{{isActive('user.index', User::STT_ACTIVE)}}"><a href="{{route('user.index', ['status' => User::STT_ACTIVE])}}">{{trans('manage.all')}}</a></li>
<li class="{{isActive('user.index', User::STT_BANNED)}}"><a href="{{route('user.index', ['status' => User::STT_BANNED])}}">{{trans('manage.ban')}}</a></li>
<li class="{{isActive('user.index', User::STT_DISABLE)}}"><a href="{{route('user.index', ['status' => User::STT_DISABLE])}}">{{trans('manage.disable')}}</a></li>
<li class="{{isActive('user.index', User::STT_TRASH)}}"><a href="{{route('user.index', ['status' => User::STT_TRASH])}}">{{trans('manage.trash')}}</a></li>
@stop

@section('table_nav')
@include('manage.parts.table_nav', ['action_btns' => ['destroy', 'restore', 'remove']])
@stop

@section('content')

{!! show_messes() !!}
@if(!$items->isEmpty())
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th width="30"><input type="checkbox" class="check_all"/></th>
                <th>ID {!! link_order('id') !!}</th>
                <th>{{trans('manage.name')}} {!! link_order('name') !!}</th>
                <th>{{trans('manage.email')}} {!! link_order('email') !!}</th>
                <th>{{trans('manage.account_type')}} {!! link_order('type') !!}</th>
                <th>{{trans('manage.password')}}</th>
                <th>{{trans('manage.role')}}</th>
                <th>{{trans('manage.status')}} {!! link_order('status') !!}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td><input type="checkbox" name="check_items[]" class="check_item" value="{{ $item->id }}" /></td>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->type }}</td>
                <td>*************</td>
                <td>{{ $item->getRoles() }}</td>
                <td>{{ $item->status() }}</td>
                <td>
                    @if(cando('edit_my_user', $item->id))
                    <a href="{{route('user.edit', ['id' => $item->id])}}" class="btn btn-sm btn-info" title="{{trans('manage.edit')}}"><i class="fa fa-edit"></i></a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p>{{trans('manage.no_item')}}</p>
@endif

@stop

