@extends('layout.member')
@section('title', $title)
@section('description', $description)
@section('content')
<?php $lang = json_encode(["data" => app()->getLocale()]); ?>
<div class="member-wrapper" id="member-content">
    @include('elements.member_sidebar')
    <div class="content-wrapper chat">
        <div id="chat-component">
            <consultant-component
                :customers="{{ $customers }}"
                :consultants="{{ $consultants }}"
                :contacted-users="{{ json_encode($contactedUsers) }}"
                :auth-user="{{ $authConsultant }}"
                :selected-id="{{ $single }}"
                :access-key="{{ json_encode(['key' => getenv('CURRENCY_LAYER_KEY')]) }}"
                :find-route="{{ json_encode(['route' => route('find-consultant')]) }}"
                :lang="{{ $lang }}"
            >
            </consultant-component>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://media.twiliocdn.com/sdk/js/chat/v3.3/twilio-chat.min.js"></script>
<script src="{{ asset('js/app.js')}}"></script>
@endsection