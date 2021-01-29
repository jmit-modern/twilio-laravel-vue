@extends('layout.private')
@section('title', $title)
@section('description', $description)
@section('content')
<div class="member-wrapper">
  @include('elements.admin_sidebar')
  <div class="content-wrapper adminprof">
    <div class="single-page">
      <div class="pages-heading">
        <h2>@lang('admin_sidebar.transactions')</h2>
      </div>
      <div class="pages-top-sec">
        <div class="sort-section">
          <div class="dropdown-box">
            <label for="consultant">@lang('member.consultant'): </label>
            <select id="consultant" name="consultant" class="form-control">
              <option {{$search['consultant'] == 'All' ? 'selected' : ''}}>@lang('member.all')</option>
              @foreach($consultants as $consultant)
              <option value="{{$consultant->id}}" {{$consultant->id == $search['consultant'] ? 'selected' : ''}}>
                {{$consultant->user->first_name}} {{$consultant->user->last_name}}
              </option>
              @endforeach
            </select>
          </div>
          <div class="dropdown-box">
            <label for="date">@lang('member.date'): </label>
            <input type="text" class="form-control date-picker" id="desktop_date" readonly>
          </div>
          <div class="ml-auto">
            <button id="desktop_filter" class="filter_btn">@lang('member.filter')</button>
          </div>
        </div>
      </div>
      <div class="filter-sec">
        <div class="filter-header">
          <p>{{count($transactions)}} @lang('member.results')</p>
          <button id="show_filter" class="filter_btn">@lang('member.filter')</button>
        </div>
        <div class="filter-body">
          <div class="dropdown-box">
            <label>@lang('member.consultant'):</label>
            <select class="consultant">
              <option {{$search['consultant'] == 'All' ? 'selected' : ''}}>@lang('member.all')</option>
              @foreach($consultants as $consultant)
              <option value="{{$consultant->id}}" {{$consultant->id == $search['consultant'] ? 'selected' : ''}}>
                {{$consultant->user->first_name}} {{$consultant->user->last_name}}
              </option>
              @endforeach
            </select>
          </div>
          <div class="dropdown-box">
            <label>@lang('member.date'):</label>
            <input type="text" class="form-control date-picker" id="mobile_date" readonly>
          </div>
          <button id="mobile_filter" class="filter_btn reversed">@lang('member.filter')</button>
        </div>
      </div>
      <div class="status-section admin-page">
        <table class="table table-borderless responsive" id="transaction">
          <thead class="table-header">
            <th>@lang('admin.payer')</th>
            <th>@lang('admin.receiver')</th>
            <th>ID NR</th>
            <th>@lang('member.price')</th>
            <th>@lang('member.date-time')</th>
          </thead>
          <tbody>
            @foreach ($transactions as $transaction)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar"></div>
                  <span>{{$transaction->payer_name}}</span>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar"></div>
                  <span>{{$transaction->receiver_name}}</span>
                </div>
              </td>
              <td>{{$transaction->transaction_id}}</td>
              <td>{{$transaction->amount}} kr</td>
              <td>{{$transaction->created_at}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/MonthPicker.min.js') }}"></script>
<script>
  const data = @json($transactions);
  const search = @json($search);
	transactions(data, search);
</script>
@endsection
