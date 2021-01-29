<?php $lang = app()->getLocale();?>
<aside class="main-sidebar">
	<section class="sidebar">
		<ul class="sidebar-menu" data-widget="tree">
			<li class="{{$active =='0'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/dashboard') : url('/no/oversikt') }}" class="side-link">
					@if($active == '0')
					<img src="{{ asset('images/dashboard-icon-w.svg')}}" alt="Dashboard" />
					@else
					<img src="{{ asset('images/dashboard-icon.svg')}}" alt="Dashboard" />
					@endif
					<span>@lang('member.dashboard')</span>
				</a>
			</li>
			<li class="{{$active =='1'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/sessions') : url('/no/moter') }}" class="side-link">
					@if($active == '1')
					<img src="{{ asset('images/session-icon-w.svg')}}" alt="session" />
					@else
					<img src="{{ asset('images/session-icon.svg')}}" alt="session" />
					@endif
					<span>
                        @if( calcCommonMissedNotificationsCount() > 0 )
                          <span id="wrapper_member_sidebar_calcCommonMissedNotificationsCount">
                            <span class="numberCircle" id="member_sidebar_calcCommonMissedNotificationsCount"><?php echo calcCommonMissedNotificationsCount() ?></span>
                          </span>
                        @endif
                        @lang('member.my-sessions')
                    </span>
				</a>
			</li>
			<li class="{{$active =='2'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/wallet') : url('/no/lommebok') }}" class="side-link">
					@if($active == '2')
					<img src="{{ asset('images/wallet-icon-w.svg')}}" alt="wallet" />
					@else
					<img src="{{ asset('images/wallet-icon.svg')}}" alt="wallet" />
					@endif
					<span>@lang('member.wallet')</span>
				</a>
			</li>
			<li class="{{$active =='3'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/transactions') : url('/no/transaksjoner') }}" class="side-link">
					@if($active == '3')
					<img src="{{ asset('images/transaction-icon-w.svg')}}" alt="transaction" />
					@else
					<img src="{{ asset('images/transaction-icon.svg')}}" alt="transaction" />
					@endif
					<span>@lang('member.my-transaction')</span>
				</a>
			</li>
			<li class="{{$active =='4'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/profile') : url('/no/profil') }}" class="side-link">
					@if($active == '4')
					<img src="{{ asset('images/profile-icon-w.svg')}}" alt="profile" />
					@else
					<img src="{{ asset('images/profile-icon.svg')}}" alt="profile" />
					@endif
					<span>@lang('member.profile')</span>
				</a>
			</li>
			<li class="{{$active =='5'?'active':''}}">
				<a href="{{ $lang == 'en' ? url('/member-settings') : url('/no/kontoinnstillinger') }}" class="side-link">
					@if($active == '5')
					<img src="{{ asset('images/settings-icon-w.svg')}}" alt="settings" />
					@else
					<img src="{{ asset('images/settings-icon.svg')}}" alt="settings" />
					@endif
					<span>@lang('member.settings')</span>
				</a>
			</li>
		</ul>
	</section>
</aside>
