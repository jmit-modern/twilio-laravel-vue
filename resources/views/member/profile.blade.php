@extends('layout.profile_member')
@section('title', $title)
@section('description', $description)
@section('content')
<?php
	use Jenssegers\Agent\Agent as Agent;
	$agent = new Agent();
	$lang_ = app()->getLocale();
	$lang = json_encode(["data" => app()->getLocale()]);
?>
<div class="member-wrapper" id="member-content">
	@include('elements.member_sidebar')
	<div class="content-wrapper">
		<div class="single-page">
			<div class="pages-heading">
				<h2>@lang('member.profile')</h2>
			</div>
			<div class="profile-card-left">
				<div class="profile-header">
					@if($user_profile->profile && $user_profile->profile->cover_img)
					<div class="profile-cover" style="background-image: url('{{ $user_profile->profile->cover_img}}'); background-position: center; background-repeat: no-repeat; background-size: cover;">
					@else
					<div class="profile-cover">
						<img src="{{asset('images/white-logo.svg')}}" />
					@endif
					@if($request_type == 'own')
						<button class="btn-edit-profile">@lang('member.edit-profile')</button>
					@endif
					</div>
					<div class="profile-card profile-sub-header">
						@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->avatar))
						<div class="avatar-pic" style="background-image: url('{{ $user_profile->profile->avatar}}'); background-position: center; background-repeat: no-repeat; background-size: cover;">
							@if(!is_null($user_profile->company))
							<span><img src="{{ asset('images/mortarboard-w.svg') }}" alt="no-img" /></span>
							@endif
						</div>
						@else
						<div class="avatar-pic">
							<img src="{{asset('images/white-logo.svg')}}" />
							@if(!is_null($user_profile->company))
								<span><img src="{{ asset('images/mortarboard-w.svg') }}" alt="no-img" /></span>
							@endif
						</div>
						@endif
						<div class="detail-info">
							@if($user_profile->user->role == 'consultant' && $agent->isMobile())
							{{$user_profile->hourly_rate}} p/m
							@endif
							<div class="status" id="profile-status">
								<h2>{{$user_profile->user->first_name}} {{$user_profile->user->last_name}}</h2>
								<status-component :user-profile="{{ $user_profile->user }}"></status-component>
							</div>
							<div class="star-images">
								@if($user_profile['rate'] == 5)
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-dg.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-dg.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-dg.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-dg.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-dg.png')}}" alt="no-img" /></li>
								</ul>
								@elseif($user_profile['rate'] == 4)
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-g.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-g.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-g.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-g.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
								</ul>
								@elseif($user_profile['rate'] == 3)
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-y.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-y.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-y.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
								</ul>
								@elseif($user_profile['rate'] == 2)
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-o.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-o.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
								</ul>
								@elseif($user_profile['rate'] == 1)
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-r.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
								</ul>
								@else
								<ul class="d-flex">
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
										<li><img src="{{asset('images/home/star-w.png')}}" alt="no-img" /></li>
								</ul>
								@endif
								<?php $rate = $user_profile['rate'] ? number_format((float)$user_profile['rate'], 1) : number_format(0, 1); ?>
								<p>{{ $rate }}</p>
							</div>
							@if($user_profile->profile)
								<div class="details">
									@if($user_profile->user->role == 'consultant')
										@if($user_profile->profile->profession)
											<span>
												<img src="{{ asset('images/portfolio.svg') }}" alt="no-img" />
												@foreach($categories as $category)
													@if($user_profile->profile->profession === $category->category_name)
													{{$lang_ == 'en' ? $category->category_name : $category->category_name_no}}
													@endif
												@endforeach
											</span>
										@endif
										@if($user_profile->profile->college && !$agent->isMobile())
											<span>
												<img src="{{ asset('images/mortarboard.svg') }}" alt="no-img" />
												{{$user_profile->profile->college}}
											</span>
										@endif
									@endif
									@if(!$agent->isMobile())
										@if(!is_null($user_profile->profile->from))
											<span>
												<img src="{{ asset('images/pin.svg') }}" alt="no-img" />
												@lang('member.from') {{$user_profile->profile->from}}
											</span>
										@endif
										@if(!is_null($user_profile->profile->country))
											<span>
												<img src="{{ asset('images/home.svg') }}" alt="no-img" />
												@lang('member.lives-in') {{$user_profile->profile->region}}, {{$user_profile->profile->country}}
											</span>
										@endif
										@if(!is_null($user_profile->profile->timezone))
											<span>
												<img src="{{ asset('images/clock.svg') }}" alt="no-img" />
												{{$user_profile->profile->gmt}} {{$user_profile->profile->timezone}}
											</span>
										@endif
									@else
										@if(!is_null($user_profile->profile->from))
											<span>
												<img src="{{asset('images/pin.svg')}}" alt="no-img" />
												@lang('member.from') {{$user_profile->profile->from}}
											</span>
										@endif
										@if(!$agent->isMobile())
											@if(!is_null($user_profile->profile->country))
												<span>
													<img src="{{asset('images/home.svg')}}" alt="no-img" />
													@lang('member.lives-in') {{$user_profile->profile->region}}, {{$user_profile->profile->country}}
												</span>
											@endif
											@if(!is_null($user_profile->profile->timezone))
												<span>
													<img src="{{ asset('images/clock.svg') }}" alt="no-img" />
													{{$user_profile->profile->gmt}} {{$user_profile->profile->timezone}}
												</span>
											@endif
										@endif
									@endif
								</div>
							@else
								<div class="no-details">
									@lang('member.no-details')
									<button class="btn-edit-profile btn-no-info">@lang('member.edit-details')</button>
								</div>
							@endif
						</div>
					</div>
					<div class="mobile-tab-view">
						<div class="tab about active">
							<img src="{{asset('images/profile-icon-w.svg')}}" alt="">@lang('member.about')
						</div>
						<div class="tab sessions">
							<img src="{{asset('images/comment.svg')}}" alt="">@lang('member.sessions')
						</div>
						<div class="tab reviews">
							<img src="{{asset('images/star.svg')}}" alt="">@lang('member.reviews')
						</div>
					</div>
				</div>
				<div class="profile-card about">
					<div class="header">
						<h3>@lang('member.about-me')</h3>
						<p>
							@if($user_profile->user->role == 'consultant')
								@lang('member.consultant-membership') {{$user_profile->user->created_at}}
							@else
								@lang('member.customer-membership') {{$user_profile->user->created_at}}
							@endif
						</p>
					</div>
					<div class="body">
						@if ($user_profile->profile && !is_null($user_profile->profile->description))
						{!!$user_profile->profile->description!!}
						@else
						<p>@lang('member.no-about-us')</p>
						@endif
					</div>
				</div>
				<div class="profile-card sessions">
					<div class="rate-charts">
						<div class="chart-sec">
							<div class="header">
								<h3>@lang('member.completed-sessions')</h3>
							</div>
							<div class="chart-body">
								@if($chart_info['no_data'])
									<p>@lang('member.no-statistics')</p>
								@else
								<div class="completed-session-chart" id="completed-session-chart"></div>
								@endif
							</div>
						</div>
						@if(Auth::check() && auth()->user()->role == 'consultant' && !$chart_info['no_data'])
						<div class="chart-sec">
							<div class="header">
								<h3>@lang('member.response-rate')</h3>
							</div>
							<div class="chart-body">
								<div class="response-rate-chart" id="response-rate-chart"></div>
							</div>
						</div>
						@endif
					</div>
				</div>
				<div class="profile-card reviews">
					<div class="header">
						<h3>@lang('member.reviews')</h3>
					</div>
					<div class="body review-sec">
						<?php $count = count($review_info); ?>
						@if($count > 0)
							@foreach($review_info as $key => $review)
								@if($key == 0)
								<div class="review-group">
								@elseif($key %2 == 0)
								</div>
								<div class="review-group">
								@endif						
								<div class="review" style="{{$key > 5 ? 'display: none' : ''}}">
									<div class="review-header">
										<div class="review-personal-info">
											@if($review->type == 'CUSTOCON' && !is_null($review->customer->profile) && !is_null($review->customer->profile->avatar))
											<div class="review-avatar mr-3" style="background-image: url('{{ $review->customer->profile->avatar }}'); background-position: center; background-repeat: no-repeat; background-size: cover;"></div>
											@elseif($review->type == 'CONTOCUS' && !is_null($review->consultant->profile) && !is_null($review->consultant->profile->avatar))
											<div class="review-avatar mr-3" style="background-image: url('{{ $review->consultant->profile->avatar }}'); background-position: center; background-repeat: no-repeat; background-size: cover;"></div>
											@else
											<div class="review-avatar mr-3">
												<img src="{{asset('images/white-logo.svg')}}" />
											</div>
											@endif
											<div class="review-info">
												@if($review->type == 'CUSTOCON' && $review->customer->user)
												<p class="m-0"><b>{{$review->customer->user->first_name}} {{$review->customer->user->last_name}}</b></p>
												@elseif($review->type == 'CONTOCUS' && $review->consultant->user)
												<p class="m-0"><b>{{$review->consultant->user->first_name}} {{$review->consultant->user->last_name}}</b></p>
												@endif
												<?php
													$newDate = date('M d, Y', strtotime($review->created_at));
													$newDate = $lang_ != 'en' ? str_replace("May","Mai", $newDate) : $newDate;
													$newDate = $lang_ != 'en' ? str_replace("Oct","Okt", $newDate) : $newDate;
													$newDate = $lang_ != 'en' ? str_replace("Dec","Des", $newDate) : $newDate;
												?>
												<p class="m-0">
													@if($review->session < 2)
													{{$review->session}} @lang('member.session')
													@else
													{{$review->session}} @lang('member.sessions')
													@endif
													&#183; {{$newDate}}
												</p>
											</div>
										</div>
										<div class="review-rate d-flex">
											@if($review->rate > 4.5)
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-dg.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-dg.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-dg.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-dg.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-dg.png')}}" alt="no-image"/>
											</div>
											@elseif($review->rate > 3.5)
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-g.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-g.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-g.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-g.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
											</div>
											@elseif($review->rate > 2.5)
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-y.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-y.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-y.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
											</div>
											@elseif($review->rate > 1.5)
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-o.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-o.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
											</div>
											@elseif($review->rate > 0.5)
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-r.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
											</div>
											@else
											<div class="rate-stars d-flex pr-2">
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
												<img src="{{asset('images/home/star-w.png')}}" alt="no-image"/>
											</div>
											@endif
											{{$review->rate}}.0
										</div>
									</div>
									<div class="review-body">
										{{$review->description}}
									</div>
								</div>
								@if($key == $count -1)
								</div>
								@endif
							@endforeach
							@if($count > 6)
							<div id="loadMore" style="">
								<a href="#">@lang('member.view-more')</a>
							</div>
							@endif
						@else
							<p>@lang('member.no-reviews')</p>
						@endif
					</div>
				</div>		
				<div class="modal fade" id="edit-profile-modal" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h3>@lang('member.edit-profile')</h3>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							</div>
							<div class="modal-body">
								<div class="edit-cover-photo">
									<div class="imageupload">
										<label class="btn cover-file">
											<img src="{{asset('images/photo-camera.svg')}}" />
											<input type="file" id="upload_cover" name="image-file">
										</label>
										@if($user_profile->profile && $user_profile->profile->cover_img)
										<label class="btn delete-file">
											<img src="{{asset('images/trash.svg')}}" />
										</label>
										@endif
									</div>
								</div>
								<div class="edit-profile-photo my-3">
									<div class="preview-profile-photo">
										@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->avatar))
										<img src="{{asset('images/white-logo.svg')}}" />
										@endif
									</div>
									<div class="profile-photo">
										<label>@lang('member.edit-profile-photo')</label>
										<p>@lang('member.edit-profile-photo-text')</p>
										<label class="btn upload-profile-photo">
											@lang('admin.upload')
											<input type="file" id="upload_profile" name="image-file">
										</label>
										@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->avatar))
										<label class="btn upload-profile-photo" id="delete_profile_avatar">
											@lang('member.delete')
										</label>
										@endif
									</div>
								</div>
								<div class="basic-info">
									<label>@lang('member.profile_settings')</label>
									<form class="basic-form">
										<div class="row m-0">
											<div class="form-group">
												<label>@lang('member.first_name')</label>
												<input type="text" id="first_name" name="first_name" value="{{$user_profile->user->first_name}}" required/>
											</div>
											<div class="form-group">
												<label>@lang('member.last_name')</label>
												<input type="text" id="last_name" name="last_name" value="{{$user_profile->user->last_name}}" required/>
											</div>
										</div>
										<div class="row m-0">
											<div class="form-group">
												<label>@lang('member.email')</label>
												<input type="text" id="email" name="email" value="{{$user_profile->user->email}}" required/>
											</div>
											<div class="form-group">
												<label>@lang('member.phone')</label>
												<input type="text" id="phone" name="phone" value="{{$user_profile->user->phone}}" required/>
											</div>
										</div>
										@if($user_profile->user->role == 'consultant')
										<div class="row m-0">
											<div class="form-group">
												<label>@lang('member.profession')</label>
												<select class="profession" name="profession" required>
													@foreach($categories as $category)
														@if($lang_ == 'en')
														<option @if($user_profile->profile && $user_profile->profile->profession == $category->category_name) {{'selected'}} @endif value="{{$category->category_name}}">{{$category->category_name}}</option>
														@else
														<option @if($user_profile->profile && $user_profile->profile->profession == $category->category_name_no) {{'selected'}} @endif value="{{$category->category_name}}">{{$category->category_name_no}}</option>
														@endif
													@endforeach
												</select>
											</div>
											<div class="form-group">
												<label>@lang('member.college')</label>
												<select class="university-list" name="university" required></select>
											</div>
										</div>
										@endif
										<div class="row m-0">
											<div class="form-group">
												<label>@lang('member.timezone')</label>	
												<select id="timezone" name="timezone" required></select>
											</div>
											<div class="form-group">
												<label>@lang('member.from')</label>
												@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->from))
												<select class="crs-country" id="from" name="from" data-region-id="hiddien_region" data-default-value="{{$user_profile->profile->from}}"></select>
												<select id="hiddien_region" hidden></select>
												@else
												<select class="crs-country" id="from" name="from" data-region-id="hiddien_region"></select>
												<select id="hiddien_region" hidden></select>
												@endif
											</div>
										</div>
										<div class="row m-0">
											<div class="form-group">
												<label>@lang('member.country')</label>
												@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->country))
												<select class="crs-country" id="country" name="country" data-region-id="region" data-default-value="{{$user_profile->profile->country}}" required></select>
												@else
												<select class="crs-country" id="country" name="country" data-region-id="region" required></select>
												@endif
											</div>
											<div class="form-group">
												<label>@lang('member.region')</label>
												@if(!is_null($user_profile->profile) && !is_null($user_profile->profile->region))
												<select id="region" name="region" data-default-value="{{$user_profile->profile->region}}" required></select>
												@else
												<select id="region" name="region" required></select>
												@endif
											</div>
										</div>
										<div class="row m-0 about">
											<div class="form-group">
												<label>@lang('member.about-me')</label>
												<textarea id="description" name="description"></textarea>
											</div>
										</div>
										<input type="submit" class="btn-save" id="profile_save" value="@lang('member.save')">
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<svg width="0" height="0" version="1.1" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<linearGradient id="MyGradient" x1="0%" y1="0%" x2="0%" y2="100%">
				<stop offset="5%" stop-color="#6c9cff" />
				<stop offset="95%" stop-color="#8773ff" />
			</linearGradient>
		</defs>
	</svg>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/app.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.5.2/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://unpkg.com/gijgo@1.9.11/js/gijgo.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.11/c3.js"></script>
<script src="{{ asset('js/intlTelInput.min.js')}}"></script>
<script src="{{ asset('js/jquery.crs.min.js')}}"></script>
<script src="{{ asset('js/timezones.full.min.js')}}"></script>
<script src="{{ asset('js/member-gotoconsult.js')}}"></script>
<script>
	public();
	const user_profile = @json($user_profile);
	const review_info = @json($review_info);
	const chart_info = @json($chart_info);
	const img_group = {
		"profile-icon-w": @json(asset('images/profile-icon-w.svg')),
		"comment-w": @json(asset('images/comment-w.svg')),
		"star-w": @json(asset('images/star-w.svg')),
		"profile-icon": @json(asset('images/profile-icon.svg')),
		"comment": @json(asset('images/comment.svg')),
		"star": @json(asset('images/star.svg'))
	};
	profile(user_profile, review_info, chart_info, img_group);
</script>
@endsection
