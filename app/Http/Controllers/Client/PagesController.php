<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use App\Models\MissedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

use Klarna\Rest\Transport\Connector;
use Klarna\Rest\Transport\ConnectorInterface;
use Klarna\Rest\Checkout\Order;
use Klarna\Rest\OrderManagement\Order as OrderInStore;

use App\User;
use App\Models\Page;
use App\Models\Categories;
use App\Models\Consultant;
use App\Models\Customer;
use App\Models\Company;
use App\Models\ChargingTransactions;
use App\Models\Transactions;
use App\Models\Review;
use App\Models\Session;
use App\Models\Requests;
use App\Models\Profile;
use App\Models\Education;
use App\Models\TwilioChannels;
use App\Models\Experience;
use App\Models\Certificate;

use App\Mail\UserRegister;
use App\Mail\ForgotPassword;
use App\Mail\ConsultantRegisterSuccess;
use App;
use Auth;
use DateTime;
use Agent;
use Hash;
use EmailChecker;
//use Session;

class PagesController extends Controller
{
    public function index() {
        if(Auth::check()){
            if (App::getLocale() == 'en') {
                $url = Auth::user()->role == 'admin' ? '/admin-dashboard' : '/dashboard';
            } else {
                $url = Auth::user()->role =='admin' ? '/no/admin-dashbord' : '/no/oversikt';
            }
            return redirect($url);
        }
        // $users = User::join('consultants', function($join) {
        //     $join->on('users.id', 'consultants.user_id')
        //     ->where('consultants.currency', 'NOK');
        // })
        // ->join('profile', function($join) {
        //     $join->on('consultants.profile_id', 'profile.id')
        //     ->where('profile.from', 'Norway');
        // })
        // ->where('active', 1)
        // ->select('users.first_name', 'users.id', 'profile.zip_code', 'consultants.hourly_rate')
        // ->where('role', 'consultant')->get()->toArray();

        $page = Page::where('id','63')->first();
        $data = json_decode($page->page_body);
        $categories = [];
        $review_list = [];
        $customer_reviews = [];
        $consultant_reviews = [];
        $reviews = Review::get();
        foreach ($reviews as $review) {
            if ($review->type == "CUSTOCON") {
                $user = Customer::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['customer'] = $user;
                array_push($customer_reviews, $review);
            } else {
                $user = Consultant::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['consultant'] = $user;
                array_push($consultant_reviews, $review);
            }
        }
        if (count($customer_reviews) > 0) {
            if (count($customer_reviews) > 8) {
                $a = array_rand($customer_reviews, 9);
            } elseif (count($customer_reviews) > 1) {
                $a = array_rand($customer_reviews, count($customer_reviews));
            } else {
                $a = ["0"];
            }

            foreach ($a as $index) {
                array_push($review_list, $customer_reviews[$index]);
            }
        }
        if (count($consultant_reviews) > 0) {
            if (count($consultant_reviews) > 8) {
                $b = array_rand($consultant_reviews, 9);
            } elseif (count($consultant_reviews) > 1) {
                $b = array_rand($consultant_reviews, count($consultant_reviews));
            } else {
                $b = ["0"];
            }

            foreach ($b as $index) {
                array_push($review_list, $consultant_reviews[$index]);
            }
        }
        $review_count = count($review_list);
        return view('pages.home', compact('categories','data', 'review_list', 'review_count'), ['title' => $page->meta_title, 'description' => $page->meta_description]);
    }

    public function updateLang(Request $request) {
        $url = $request->lang == 'en' ? '/'.$request->address : '/no/'.$request->address;
        App::setLocale($request->lang);
        session()->put('locale', $request->lang);
        return redirect($url);
    }

    public function category_info(Request $request) {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $category = Categories::where('category_url', $request->type)->first();
        $consultants = Consultant::whereHas('profile', function($q) use ($request) {
            $q->where('profession', $request->type);
        })->whereHas('user', function($q) {
            $q->where('active', 1);
        })->with('profile', 'user')->get();

        $page = Page::where('id', 1)->first();
        $data = json_decode($page->page_body);
        $countries = [];
        $profiles = Profile::get();
        foreach ($profiles as $profile) {
            if (!in_array($profile->country, $countries)) {
                array_push($countries, $profile->country);
            }
        }
        $search = [
            'name' => 'null',
            'category' => $request->type,
            'price' => 'Default',
            'status' => 'All',
            'country' => 'All'
        ];
        $review_list = [];
        $customer_reviews = [];
        $consultant_reviews = [];
        $reviews = Review::get();
        foreach ($reviews as $review) {
            if ($review->type == "CUSTOCON") {
                $user = Customer::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['customer'] = $user;
                array_push($customer_reviews, $review);
            } else {
                $user = Consultant::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['consultant'] = $user;
                array_push($consultant_reviews, $review);
            }
        }
        if (count($customer_reviews) > 0) {
            if (count($customer_reviews) > 8) {
                $a = array_rand($customer_reviews, 9);
            } elseif(count($customer_reviews) > 1) {
                $a = array_rand($customer_reviews, count($customer_reviews));
            } else {
                $a = ["0"];
            }

            foreach ($a as $index) {
                array_push($review_list, $customer_reviews[$index]);
            }
        }
        if (count($consultant_reviews) > 0) {
            if (count($consultant_reviews) > 8) {
                $b = array_rand($consultant_reviews, 9);
            } elseif (count($consultant_reviews) > 1) {
                $b = array_rand($consultant_reviews, count($consultant_reviews));
            } else {
                $b = ["0"];
            }

            foreach ($b as $index) {
                array_push($review_list, $consultant_reviews[$index]);
            }
        }
        $review_count = count($review_list);
        return view('pages.category_info', compact('category', 'consultants', 'data', 'countries', 'search', 'review_list', 'review_count'), [
            'title' => App::getLocale() == 'en' ? 'GoToConsult - '.$category->meta_title : 'GoToConsult - '.$category->no_meta_title,
            'description' => $category->meta_description
        ]);
    }

    public function categorySearch(Request $request) {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $category = Categories::where('category_url', $request->category)->first();
        $consultants = Consultant::whereHas('profile', function($q) use ($request) {
            $q->where('profession', $request->category);
        })->whereHas('user', function($q) {
            $q->where('active', 1);
        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
        if ($request->status != 'All') { //AC -> category + status + price
            $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                $q->where('profession', $request->category);
            })->whereHas('user', function($q) use ($request) {
                if ($request->status != 'busy') {
                    $q->where('active', 1)->where('status', $request->status);
                } else {
                    $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                }
            })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
            if ($request->country != 'All') { //AD -> category + status + country + price
                $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                    $q->where('profession', $request->category)->where('country', $request->country);
                })->whereHas('user', function($q) use ($request) {
                    if ($request->status != 'busy') {
                        $q->where('active', 1)->where('status', $request->status);
                    } else {
                        $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                    }
                })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                if ($request->name != 'null') { // ADE -> category + status + country + name + price
                    $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category)->where('country', $request->country);
                    })->whereHas('user', function($q) use ($request) {
                        if ($request->status != 'busy') {
                            $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        } else {
                            $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        }
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                }
            } else { //AE -> category + status + name + price
                if ($request->name != 'null') {
                    $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category);
                    })->whereHas('user', function($q) use ($request) {
                        if ($request->status != 'busy') {
                            $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        } else {
                            $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        }
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                }
            }
        } else {
            if ($request->country != 'All') { //AD -> category + country + price
                $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                    $q->where('profession', $request->category)->where('country', $request->country);
                })->whereHas('user', function($q) {
                    $q->where('active', 1);
                })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                if ($request->name != 'null') { // ADE -> category + country + name + price
                    $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category)->where('country', $request->country);
                    })->whereHas('user', function($q) use ($request) {
                        $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                }
            } else { // category + name + price
                if ($request->name != 'null') { //AE
                    $consultants = Consultant::whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category);
                    })->whereHas('user', function($q) use ($request) {
                        $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile')->get();
                }
            }
        }

        $page = Page::where('id', 1)->first();
        $data = json_decode($page->page_body);
        $countries = [];
        $profiles = Profile::get();
        foreach ($profiles as $profile) {
            if (!in_array($profile->country, $countries)) {
                array_push($countries, $profile->country);
            }
        }
        $search = [
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'status' => $request->status,
            'country' => $request->country
        ];
        $review_list = [];
        $customer_reviews = [];
        $consultant_reviews = [];
        $reviews = Review::get();
        foreach ($reviews as $review) {
            if ($review->type == "CUSTOCON") {
                $user = Customer::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['customer'] = $user;
                array_push($customer_reviews, $review);
            } else {
                $user = Consultant::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['consultant'] = $user;
                array_push($consultant_reviews, $review);
            }
        }
        if (count($customer_reviews) > 0) {
            if (count($customer_reviews) > 8) {
                $a = array_rand($customer_reviews, 9);
            } elseif(count($customer_reviews) > 1) {
                $a = array_rand($customer_reviews, count($customer_reviews));
            } else {
                $a = ["0"];
            }

            foreach ($a as $index) {
                array_push($review_list, $customer_reviews[$index]);
            }
        }
        if (count($consultant_reviews) > 0) {
            if (count($consultant_reviews) > 8) {
                $b = array_rand($consultant_reviews, 9);
            } elseif (count($consultant_reviews) > 1) {
                $b = array_rand($consultant_reviews, count($consultant_reviews));
            } else {
                $b = ["0"];
            }

            foreach ($b as $index) {
                array_push($review_list, $consultant_reviews[$index]);
            }
        }
        $review_count = count($review_list);
        return view('pages.category_info', compact('category', 'consultants', 'data', 'countries', 'search', 'review_list', 'review_count'), [
            'title' => App::getLocale() == 'en' ? 'GoToConsult - '.$category->meta_title : 'GoToConsult - '.$category->no_meta_title,
            'description' => App::getLocale() == 'en' ? $category->meta_description : $category->no_meta_description
        ]);
    }

    public function features() {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $page = Page::where('id','10')->first();
        $data = json_decode($page->page_body);
        $review_list = [];
        $customer_reviews = [];
        $consultant_reviews = [];
        $reviews = Review::get();
        foreach ($reviews as $review) {
            if ($review->type == "CUSTOCON") {
                $user = Customer::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['customer'] = $user;
                array_push($customer_reviews, $review);
            } else {
                $user = Consultant::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['consultant'] = $user;
                array_push($consultant_reviews, $review);
            }
        }
        if (count($customer_reviews) > 0) {
            if (count($customer_reviews) > 8) {
                $a = array_rand($customer_reviews, 9);
            } elseif (count($customer_reviews) > 1) {
                $a = array_rand($customer_reviews, count($customer_reviews));
            } else {
                $a = ["0"];
            }

            foreach ($a as $index) {
                array_push($review_list, $customer_reviews[$index]);
            }
        }
        if (count($consultant_reviews) > 0) {
            if (count($consultant_reviews) > 8) {
                $b = array_rand($consultant_reviews, 9);
            } elseif (count($consultant_reviews) > 1) {
                $b = array_rand($consultant_reviews, count($consultant_reviews));
            } else {
                $b = ["0"];
            }

            foreach ($b as $index) {
                array_push($review_list, $consultant_reviews[$index]);
            }
        }
        $review_count = count($review_list);
        return view('pages.features', compact('data', 'review_list', 'review_count'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function become_consultant() {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $categories = Categories::all();
        $count = Categories::count();
        $page = Page::where('id','2')->first();
        $data = json_decode($page->page_body);
        $terms_page = Page::where('id', '9')->first();
        $terms = json_decode($terms_page->page_body);
        return view('pages.become_consultant', compact('categories', 'count', 'data', 'terms'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function about_us() {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $page = Page::where('id','3')->first();
        $data = json_decode($page->page_body);
        $review_list = [];
        $customer_reviews = [];
        $consultant_reviews = [];
        $reviews = Review::get();
        foreach ($reviews as $review) {
            if ($review->type == "CUSTOCON") {
                $user = Customer::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['customer'] = $user;
                array_push($customer_reviews, $review);
            } else {
                $user = Consultant::where('user_id', $review->sender_id)->with(['profile', 'user'])->first();
                $review['consultant'] = $user;
                array_push($consultant_reviews, $review);
            }
        }
        if (count($customer_reviews) > 0) {
            if (count($customer_reviews) > 8) {
                $a = array_rand($customer_reviews, 9);
            } elseif (count($customer_reviews) > 1) {
                $a = array_rand($customer_reviews, count($customer_reviews));
            } else {
                $a = ["0"];
            }

            foreach ($a as $index) {
                array_push($review_list, $customer_reviews[$index]);
            }
        }
        if (count($consultant_reviews) > 0) {
            if (count($consultant_reviews) > 8) {
                $b = array_rand($consultant_reviews, 9);
            } elseif (count($consultant_reviews) > 1) {
                $b = array_rand($consultant_reviews, count($consultant_reviews));
            } else {
                $b = ["0"];
            }

            foreach ($b as $index) {
                array_push($review_list, $consultant_reviews[$index]);
            }
        }
        $review_count = count($review_list);
        return view('pages.about_us', compact('data', 'review_list', 'review_count'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function privacy(){
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $page = Page::where('id','6')->first();
        $data = json_decode($page->page_body);
        return view('pages.privacy', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function terms_customer() {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $page = Page::where('id','5')->first();
        $data = json_decode($page->page_body);
        return view('pages.terms_customer', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function terms_provider() {
        if(Auth::check()){
            return App::getLocale() == 'en' ? ( Auth::user()->role == 'admin' ? redirect('/admin-dashboard') : redirect('/dashboard') ) : ( Auth::user()->role !='admin' ? redirect('/no/oversikt') : redirect('/no/admin-dashbord') );
        }
        $page = Page::where('id','9')->first();
        $data = json_decode($page->page_body);
        return view('pages.terms_provider', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
        ]);
    }

    public function findConsultant() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $consultants = Consultant::with('user', 'profile', 'company')->where('user_id', '!=', auth()->user()->id)->whereHas('user', function($q) {
            $q->where('active', 1);
        })->get();

        $countries = [];
        $profiles = Profile::get();
        foreach ($profiles as $profile) {
            if (!in_array($profile->country, $countries)) {
                array_push($countries, $profile->country);
            }
        }
        $search = [
            'name' => 'null',
            'category' => 'All',
            'price' => 'Default',
            'status' => 'All',
            'country' => 'All'
        ];
        return view('member.find_consultant', compact('consultants', 'countries', 'search'), [
            'title' => App::getLocale() == 'en' ? 'Find Consultant' : 'Finn Konsulent',
            'description' => '',
            'active' => ''
        ]);
    }

    public function findConsultantSearch(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $consultants = null;
        if ($request->category != "All") {
            $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                $q->where('profession', $request->category);
            })->whereHas('user', function($q) {
                $q->where('active', 1);
            })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
            if ($request->status != 'All') { //AC -> category + status + price
                $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                    $q->where('profession', $request->category);
                })->whereHas('user', function($q) use ($request) {
                    if ($request->status != 'busy') {
                        $q->where('active', 1)->where('status', $request->status);
                    } else {
                        $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                    }
                })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                if ($request->country != 'All') { //AD -> category + status + country + price
                    $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category)->where('country', $request->country);
                    })->whereHas('user', function($q) use ($request) {
                        if ($request->status != 'busy') {
                            $q->where('active', 1)->where('status', $request->status);
                        } else {
                            $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                        }
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    if ($request->name != 'null') { // ADE -> category + status + country + name + price
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('profession', $request->category)->where('country', $request->country);
                        })->whereHas('user', function($q) use ($request) {
                            if ($request->status != 'busy') {
                                $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            } else {
                                $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            }
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                } else { //AE -> category + status + name + price
                    if ($request->name != 'null') {
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('profession', $request->category);
                        })->whereHas('user', function($q) use ($request) {
                            if ($request->status != 'busy') {
                                $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            } else {
                                $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            }
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                }
            } else {
                if ($request->country != 'All') { //AD -> category + country + price
                    $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                        $q->where('profession', $request->category)->where('country', $request->country);
                    })->whereHas('user', function($q) {
                        $q->where('active', 1);
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    if ($request->name != 'null') { // ADE -> category + country + name + price
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('profession', $request->category)->where('country', $request->country);
                        })->whereHas('user', function($q) use ($request) {
                            $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                } else { // category + name + price
                    if ($request->name != 'null') { //AE
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('profession', $request->category);
                        })->whereHas('user', function($q) use ($request) {
                            $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                }
            }
        } else {
            $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('user', function($q) {
                $q->where('active', 1);
            })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
            if ($request->status != 'All') { //AC -> status + price
                $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('user', function($q) use ($request) {
                    if ($request->status != 'busy') {
                        $q->where('active', 1)->where('status', $request->status);
                    } else {
                        $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                    }
                })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                if ($request->country != 'All') { //AD -> status + country + price
                    $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                        $q->where('country', $request->country);
                    })->whereHas('user', function($q) use ($request) {
                        if ($request->status != 'busy') {
                            $q->where('active', 1)->where('status', $request->status);
                        } else {
                            $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call');
                        }
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    if ($request->name != 'null') { // ADE -> status + country + name + price
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('country', $request->country);
                        })->whereHas('user', function($q) use ($request) {
                            if ($request->status != 'busy') {
                                $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            } else {
                                $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            }
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                } else { //AE -> status + name + price
                    if ($request->name != 'null') {
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('user', function($q) use ($request) {
                            if ($request->status != 'busy') {
                                $q->where('active', 1)->where('status', $request->status)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            } else {
                                $q->where('active', 1)->where('status', 'In a chat')->orWhere('status', 'In a call')->orWhere('status', 'In a Video call')->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                            }
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                }
            } else {
                if ($request->country != 'All') { //AD -> country + price
                    $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                        $q->where('country', $request->country);
                    })->whereHas('user', function($q) {
                        $q->where('active', 1);
                    })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    if ($request->name != 'null') { // ADE -> country + name + price
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('profile', function($q) use ($request) {
                            $q->where('country', $request->country);
                        })->whereHas('user', function($q) use ($request) {
                            $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                } else { // name + price
                    if ($request->name != 'null') { //AE
                        $consultants = Consultant::where('user_id', '!=', auth()->user()->id)->whereHas('user', function($q) use ($request) {
                            $q->where('active', 1)->where('first_name', 'LIKE', '%'.$request->name.'%')->orWhere('last_name', 'LIKE', '%'.$request->name.'%');
                        })->orderBy('hourly_rate', $request->price == 'low-high' ? 'asc' : 'desc')->with('user', 'profile', 'company')->get();
                    }
                }
            }
        }

        $countries = [];
        $profiles = Profile::get();
        foreach ($profiles as $profile) {
            if (!in_array($profile->country, $countries)) {
                array_push($countries, $profile->country);
            }
        }
        $search = [
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'status' => $request->status,
            'country' => $request->country
        ];
        return view('member.find_consultant', compact('consultants', 'countries', 'search'), [
            'title' => App::getLocale() == 'en' ? 'Find Consultant' : 'Finn Konsulent',
            'description' => '',
            'active' => ''
        ]);
    }

    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
    public function dashboard() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        if (Auth::user()->role == "consultant") {
            $user_info = Consultant::where('user_id', Auth::user()->id)->first();
            $dt = new DateTime;
            $today_start = clone $dt->setTime(0, 0, 0);
            $today_end = $dt->setTime(23, 59, 59);
            $transactions = Transactions::where('receiver_id', $user_info->id)->where('created_at', '>=', $today_start)->where('created_at', '<=', $today_end)->get();
            $total_amount = 0;
            foreach ($transactions as $transaction) {
                $total_amount += $transaction->amount;
            }
            $earning = $total_amount;

            $recent_sessions = [];
            $sessions = Session::where('consultant_id', $user_info->id)->orWhere('customer_id', Auth::user()->id)->latest('created_at')->take(5)->get();
            foreach ($sessions as $session) {
                if ($session->type == 'CUSTOCON') {
                    $customer = Customer::where('user_id', $session->customer_id)->with('profile', 'user')->first();
                    array_push($recent_sessions, $customer);
                } else {
                    if ($session->customer_id == Auth::user()->id) {
                        $consultant = Consultant::where('id', $session->consultant_id)->with('profile', 'user', 'company')->first();
                    } else if ($session->consultant_id == $user_info->id) {
                        $consultant = Consultant::where('user_id', $session->customer_id)->with('profile', 'user', 'company')->first();
                    }
                    array_push($recent_sessions, $consultant);
                }
            }
            $count_sessions = count($this->unique_multidim_array($recent_sessions,'user_id'));
            $count_consultants = Consultant::where('id', '!=', $user_info->id)->whereHas('user', function($q) {
                $q->where('active', 1);
            })->with('profile', 'user', 'company')->orderBy('rate', 'desc')->take(5)->count();
        } else if (Auth::user()->role == "customer") {
            $sessions = Session::where('customer_id', Auth::user()->id)->latest('created_at')->take(5)->get();
            $recent_sessions = [];
            foreach ($sessions as $session) {
                $consultant = Consultant::where('id', $session->consultant_id)->with('profile', 'user', 'company')->first();
                array_push($recent_sessions, $consultant);
            }
            $count_sessions = count($this->unique_multidim_array($recent_sessions,'user_id'));
            $count_consultants = Consultant::whereHas('user', function($q) {
                $q->where('active', 1);
            })->with('profile', 'user', 'company')->orderBy('rate', 'desc')->take(5)->count();
            $earning = 0;
        }
        return view('member.dashboard', compact('count_sessions', 'count_consultants', 'earning'), [
            'title' => App::getLocale() == 'en' ? 'Dashboard' : 'Oversikt',
            'description' => '',
            'active' => '0'
        ]);
    }

    public function session() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }

        $single = 0;
        $consultants = Consultant::whereHas('user', function($q) {
            $q->where('active', 1);
        })->with('user', 'profile', 'company')->get();
        $customers = Customer::whereHas('user', function($q) {
            $q->where('active', 1);
        })->with('user', 'profile')->get();

        if (Auth::user()->role == 'consultant') {
            $missedNotifications= MissedNotification
                ::getByReceiverId(Auth::user()->id)
                ->select('sender_id', DB::raw('count(*) as sender_total'))
                ->groupBy('sender_id')
                ->get()
                ->toArray();
            \Session::put('missedNotifications', $missedNotifications);

            $authConsultant = Consultant::where('user_id', Auth::user()->id)->with('profile','user')->first();
            $contactedUsers = [];
            $channels = TwilioChannels::get();
            $vatPercent= 0;
            foreach($channels as $channel) {
                if ($channel->consultant_id === $authConsultant->user_id) {
                    if ($channel->direction === 'con-con') {
                        $user = Consultant::where('user_id', $channel->customer_id)->with('user', 'profile', 'company')->first();

                        $user->sender_total = 0;
                        foreach( $missedNotifications as $nextMissedNotification ) {
                            if( $nextMissedNotification['sender_id'] == (int)$user->user_id) {
                                $user->sender_total = $nextMissedNotification['sender_total'];
                            }
                        }
                        $vatPercent=  getVatPercent($user->profile, $authConsultant->profile);
                        array_push($contactedUsers, $user);
                    } else {
                        $user = Customer::where('user_id', $channel->customer_id)->with('user', 'profile', 'company')->first();

                        $user->sender_total = 0;
                        foreach( $missedNotifications as $nextMissedNotification ) {
                            if( $nextMissedNotification['sender_id'] == (int)$user->user_id) {
                                $user->sender_total = $nextMissedNotification['sender_total'];
                            }
                        }
                        $vatPercent=  getVatPercent($user->profile, $authConsultant->profile);
                        array_push($contactedUsers, $user);
                    }
                } else if ($channel->customer_id === $authConsultant->user_id) {
                    $consultant = Consultant::where('user_id', $channel->consultant_id)->whereHas('user', function($q) {
                        $q->where('active', 1);
                    })->with('user', 'profile', 'company')->first();
                    $consultant->sender_total = 0;
                    foreach( $missedNotifications as $nextMissedNotification ) {
                        if( $nextMissedNotification['sender_id'] == (int)$consultant->user_id) {
                            $consultant->sender_total = $nextMissedNotification['sender_total'];
                        }
                    }
                    $vatPercent=  getVatPercent($consultant->profile, $authConsultant->profile);
                    array_push($contactedUsers, $consultant);
                }
            }
            return view('member.consultantchat', compact('customers', 'consultants', 'authConsultant', 'single', 'contactedUsers', 'vatPercent'), [
                'title' => App::getLocale() == 'en' ? 'My Sessions' : 'Mine møter',
                'description' => '',
                'active' => '1'
            ]);
        } else {
            $missedNotifications= MissedNotification
                ::getByReceiverId(Auth::user()->id)
                ->select('sender_id', DB::raw('count(*) as sender_total'))
                ->groupBy('sender_id')
                ->get()
                ->toArray();
            \Session::put('missedNotifications', $missedNotifications);

            $authCustomer = Customer::where('user_id', Auth::user()->id)->with('profile','user')->first();
            $contactedConsultants = [];
            $channels = TwilioChannels::where('customer_id', Auth::user()->id)->get();
            $vatPercent= 0;
            foreach($channels as $channel) {
                if ($channel->customer_id === $authCustomer->user_id) {
                  $consultant = Consultant::where('user_id', $channel->consultant_id)->whereHas('user', function($q) {
                    $q->where('active', 1);
                  })->with('user', 'profile', 'company')->first();
                  $consultant->sender_total = 0;
                  foreach( $missedNotifications as $nextMissedNotification ) {
                      if( $nextMissedNotification['sender_id'] == (int)$consultant->user_id) {
                          $consultant->sender_total = $nextMissedNotification['sender_total'];
                      }
                  }
                  $vatPercent=  getVatPercent($consultant->profile, $authCustomer->profile);
                  array_push($contactedConsultants, $consultant);
                }
            }

            return view('member.customerchat', compact('consultants', 'customers', 'authCustomer', 'single', 'contactedConsultants', 'vatPercent'), [
                'title' => App::getLocale() == 'en' ? 'My Sessions' : 'Mine møter',
                'description' => '',
                'active' => '1'
            ]);
        }
    }
    public function singleSession(Request $request) {
        if(!Auth::check()){
           return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }

        $single = $request->id;

        $missedNotifications= MissedNotification
            ::getByReceiverId(Auth::user()->id)
            ->select('sender_id', DB::raw('count(*) as sender_total'))
            ->groupBy('sender_id')
            ->get()
            ->toArray();

        \Log::info(  varDump($single, ' -1 singleSession $single::') );
        \Log::info(  varDump(Auth::user()->id, ' -1 singleSession LOGGED USER Auth::user()->id::') );
        \Log::info(  varDump($missedNotifications, ' -1 singleSession missedNotifications::') );

        $consultants = Consultant::whereHas('user', function($q) {
            $q->where('active', 1);
        })->with('user', 'profile', 'company')->get();
        $customers = Customer::whereHas('user', function($q) {
            $q->where('active', 1);
        })->with('user', 'profile')->get();

        if (Auth::user()->role == 'consultant') {
            $authConsultant = Consultant::where('user_id', Auth::user()->id)->with('profile','user')->first();
            $contactedUsers = [];
            $channels = TwilioChannels::get();
            foreach($channels as $channel) {
                if ($channel->consultant_id === $authConsultant->user_id) {
                    if ($channel->direction === 'con-con') {
                        $user = Consultant::where('user_id', $channel->customer_id)->with('user', 'profile')->first();
                        array_push($contactedUsers, $user);
                    } else {
                        $user = Customer::where('user_id', $channel->customer_id)->with('user', 'profile')->first();
                        array_push($contactedUsers, $user);
                    }
                } else if ($channel->customer_id === $authConsultant->user_id) {
                    $consultant = Consultant::where('user_id', $channel->consultant_id)->with('user', 'profile')->first();
                    array_push($contactedUsers, $consultant);
                }
            }
            return view('member.consultantchat', compact('customers', 'consultants', 'authConsultant', 'single', 'contactedUsers'), [
                'title' => App::getLocale() == 'en' ? 'My Sessions' : 'Mine møter',
                'description' => '',
                'active' => '1'
            ]);
        } else {
            $authCustomer = Customer::where('user_id', Auth::user()->id)->with('profile','user')->first();
            $contactedConsultants = [];
            $channels = TwilioChannels::where('customer_id', Auth::user()->id)->get();
            $vatPercent= 0;
            foreach($channels as $channel) {
                if ($channel->customer_id === $authCustomer->user_id) {
                  $consultant = Consultant::where('user_id', $channel->consultant_id)->with('user', 'profile')->first();
                  $vatPercent=  getVatPercent($consultant->profile, $authCustomer->profile);
                  array_push($contactedConsultants, $consultant);
                }
            }

            return view('member.customerchat', compact('consultants', 'customers', 'authCustomer', 'single', 'contactedConsultants', 'vatPercent'), [
                'title' => App::getLocale() == 'en' ? 'My Sessions' : 'Mine møter',
                'description' => '',
                'active' => '1'
            ]);
        }
    }

    public function wallet() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $cur_balance = Auth::user()->balance;
        $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        $search = [
            'start' => 'null',
            'end' => 'null',
            'type' => 'null'
        ];
        $auth_user = [];
        if (auth()->user()->role == 'consultant') {
            $auth_user = Consultant::where('user_id', auth()->user()->id)->with('profile')->first();
        } else {
            $auth_user = Customer::where('user_id', auth()->user()->id)->with('profile')->first();
        }
        $currency = $auth_user->currency ? $auth_user->currency : 'NOK';
        $credits = [
            ["id" => 'card1', 'amount' => $currency == 'NOK'?100:10],
            ["id" => 'card2', 'amount' => $currency == 'NOK'?200:20],
            ["id" => 'card3', 'amount' => $currency == 'NOK'?300:30],
            ["id" => 'card4', 'amount' => $currency == 'NOK'?500:50],
            ["id" => 'card5', 'amount' => $currency == 'NOK'?1000:100],
            ["id" => 'card6', 'amount' => $currency == 'NOK'?2000:200],
            ["id" => 'card7', 'amount' => $currency == 'NOK'?5000:500],
            ["id" => 'card8', 'amount' => 0]
        ];

        return view('member.wallet', [
            'title' => App::getLocale() == 'en' ? 'My Wallet' : 'Min lommebok',
            'description' => '',
            'active' => '2',
            'transactions' => $transactions,
            'balance' => $cur_balance,
            'currency' => $currency,
            'is_popup' => 'false',
            'amount' => '3',
            'credits' => $credits,
            'search' => $search
        ]);
    }
    public function walletSearch(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $cur_balance = Auth::user()->balance;

        if ($request->start != 'null') {
            $startDate_array = explode("/", $request->input('start'));
            $startDate = "$startDate_array[2]-$startDate_array[0]-$startDate_array[1]"." 00:00:00";
            $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->where('created_at', '>=', $startDate)->orderBy('created_at', 'desc')->get();
            if ($request->end != 'null') {
                $endDate_array = explode("/", $request->input('end'));
                $endDate = "$endDate_array[2]-$endDate_array[0]-$endDate_array[1]"." 23:59:59";
                $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc')->get();
                if ($request->type != 'null') {
                    $transactions = ChargingTransactions::where('user_id', Auth::user()->id)
                                    ->whereBetween('created_at', [$startDate, $endDate])
                                    ->where(function($q) use ($request) {
                                        if ($request->type != "Klarna") {
                                            $q->where('type', '!=', 'Klarna');
                                        } else {
                                            $q->where('type', 'Klarna');
                                        }
                                    })->orderBy('created_at', 'desc')->get();
                }
            } else {
                if ($request->type != 'null') {
                    $transactions = ChargingTransactions::where('user_id', Auth::user()->id)
                                    ->where('created_at', '>=', $startDate)
                                    ->where(function($q) use ($request) {
                                        if ($request->type != "Klarna") {
                                            $q->where('type', '!=', 'Klarna');
                                        } else {
                                            $q->where('type', 'Klarna');
                                        }
                                    })->orderBy('created_at', 'desc')->get();
                }
            }
        } else if ($request->end != 'null') {
            $endDate_array = explode("/", $request->input('end'));
            $endDate = "$endDate_array[2]-$endDate_array[0]-$endDate_array[1]"." 23:59:59";
            $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->where('created_at', '<=', $endDate)->orderBy('created_at', 'desc')->get();
            if ($request->type != 'null') {
                $transactions = ChargingTransactions::where('user_id', Auth::user()->id)
                                ->where('created_at', '<=', $endDate)
                                ->where(function($q) use ($request) {
                                    if ($request->type != "Klarna") {
                                        $q->where('type', '!=', 'Klarna');
                                    } else {
                                        $q->where('type', 'Klarna');
                                    }
                                })->orderBy('created_at', 'desc')->get();
            }
        }  else if ($request->type != 'null') {
            $transactions = ChargingTransactions::where('user_id', Auth::user()->id)
            ->where(function($q) use ($request) {
                if ($request->type != "Klarna") {
                    $q->where('type', '!=', 'Klarna');
                } else {
                    $q->where('type', 'Klarna');
                }
            })->orderBy('created_at', 'desc')->get();
        } else {
            $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
        }
        $search = [
            'start' => $request->start,
            'end' => $request->end,
            'type' => $request->type
        ];
        $auth_user = [];
        if (auth()->user()->role == 'consultant') {
            $auth_user = Consultant::where('user_id', auth()->user()->id)->with('profile')->first();
        } else {
            $auth_user = Customer::where('user_id', auth()->user()->id)->with('profile')->first();
        }
        $currency = $auth_user->currency ? $auth_user->currency : 'NOK';
        $credits = [
            ["id" => 'card1', 'amount' => $currency == 'NOK'?100:10],
            ["id" => 'card2', 'amount' => $currency == 'NOK'?200:20],
            ["id" => 'card3', 'amount' => $currency == 'NOK'?300:30],
            ["id" => 'card4', 'amount' => $currency == 'NOK'?500:50],
            ["id" => 'card5', 'amount' => $currency == 'NOK'?1000:100],
            ["id" => 'card6', 'amount' => $currency == 'NOK'?2000:200],
            ["id" => 'card7', 'amount' => $currency == 'NOK'?5000:500],
            ["id" => 'card8', 'amount' => 0]
        ];

        return view('member.wallet', [
            'title' => App::getLocale() == 'en' ? 'My Wallet' : 'Min lommebok',
            'description' => '',
            'active' => '2',
            'transactions' => $transactions,
            'balance' => $cur_balance,
            'currency' => $currency,
            'is_popup' => 'false',
            'amount' => '3',
            'credits' => $credits,
            'search' => $search
        ]);
    }

    public function transactions() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $transactions = [];
        $consultants = [];
        $consultantIds = [];
        if (Auth::user()->role == 'customer') {
            $transaction_list = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
            foreach ($transaction_list as $transaction) {
                $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                if (!in_array($consultant->user->id, $consultantIds)) {
                    array_push($consultantIds, $consultant->user->id);
                    array_push($consultants, $consultant);
                }
                $transaction['consultant'] = $consultant;
                array_push($transactions, $transaction);
            }
        } else {
            $consultant = Consultant::where('user_id', Auth::user()->id)->first();
            $spendings = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
            $earnings = Transactions::where('receiver_id', $consultant->id)->orderBy('created_at', 'desc')->get();
            foreach ($spendings as $spend) {
                $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                    array_push($consultantIds, $spend['consultant']->user->id);
                    array_push($consultants, $spend['consultant']);
                }
                array_push($transactions, $transaction);
            }
            foreach ($earnings as $earning) {
                $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                    array_push($consultantIds, $earning['customer']->user->id);
                    array_push($consultants, $earning['customer']);
                }
                if (!isset($earning['customer'])) {
                    $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                    if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                        array_push($consultantIds, $earning['consultant']->user->id);
                        array_push($consultants, $earning['consultant']);
                    }
                }
                array_push($transactions, $earning);
            }
        }
        $search = [
            'name' => 'null',
            'consultant' => 'All',
            'date' => 'null',
            'type' => 'All'
        ];
        return view('member.transaction', [
            'title' => App::getLocale() == 'en' ? 'My Transactions' : 'Transaksjonene mine',
            'title' => '',
            'description' => '',
            'active' => '3',
            'transactions' => $transactions,
            'consultants' => $consultants,
            'search' => $search
        ]);
    }
    public function findObjectById($id, $arry){
        foreach ( $array as $element ) {
            if ( $id == $element->id ) {
                return $element;
            }
        }
        return false;
    }
    // Transaction Search fields should consider 'ALL' status
    public function transactionSearch(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $consultants = [];
        $consultantIds = [];
        if (Auth::user()->role == 'customer') {
            if (isset($request->name) && $request->name != 'null') {
                $transactions = [];
                $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                ->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                foreach ($transaction_list as $transaction) {
                    $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                    if (!in_array($consultant->id, $consultantIds)) {
                        array_push($consultantIds, $consultant->id);
                        array_push($consultants, $consultant);
                    }
                    $transaction['consultant'] = $consultant;
                    array_push($transactions, $transaction);
                }

                if ($request->consultant != 'null') {
                    $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                    ->where('transaction_id', 'LIKE', '%'.$request->name.'%')
                    ->where('receiver_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                    $transactions = [];
                    foreach ($transaction_list as $transaction) {
                        $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($consultant->id, $consultantIds)) {
                            array_push($consultantIds, $consultant->id);
                            array_push($consultants, $consultant);
                        }
                        $transaction['consultant'] = $consultant;
                        array_push($transactions, $transaction);
                    }

                    if ($request->date != 'null') {
                        $date_array = explode("/", $request->input('date'));
                        $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                        $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                            ->where('transaction_id', 'LIKE', '%'.$request->name.'%')
                            ->where('created_at', '<=', $date)
                            ->where('receiver_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                        $transactions = [];
                        foreach ($transaction_list as $transaction) {
                            $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($consultant->id, $consultantIds)) {
                                array_push($consultantIds, $consultant->id);
                                array_push($consultants, $consultant);
                            }
                            $transaction['consultant'] = $consultant;
                            array_push($transactions, $transaction);
                        }
                    }
                } else if($request->date != 'null') {
                    $date_array = explode("/", $request->input('date'));
                    $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                    $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                    ->where('transaction_id', 'LIKE', '%'.$request->name.'%')
                    ->where('created_at', ',=', $date)->orderBy('created_at', 'desc')->get();
                    $transactions = [];
                    foreach ($transaction_list as $transaction) {
                        $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($consultant->id, $consultantIds)) {
                            array_push($consultantIds, $consultant->id);
                            array_push($consultants, $consultant);
                        }
                        $transaction['consultant'] = $consultant;
                        array_push($transactions, $transaction);
                    }
                }
            } else if ($request->consultant != 'null') {
                $transactions = [];
                $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                ->where('receiver_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                foreach ($transaction_list as $transaction) {
                    $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                    if (!in_array($consultant->id, $consultantIds)) {
                        array_push($consultantIds, $consultant->id);
                        array_push($consultants, $consultant);
                    }

                    $transaction['consultant'] = $consultant;
                    array_push($transactions, $transaction);
                }

                if ($request->date != 'null') {
                    $date_array = explode("/", $request->input('date'));
                    $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                    $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                    ->where('created_at', '<=', $date)
                    ->where('receiver_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                    $transactions = [];
                    foreach ($transaction_list as $transaction) {
                        $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($consultant->id, $consultantIds)) {
                            array_push($consultantIds, $consultant->id);
                            array_push($consultants, $consultant);
                        }
                        $transaction['consultant'] = $consultant;
                        array_push($transactions, $transaction);
                    }
                }
            } else if ($request->date != 'null') {
                $transactions = [];
                $date_array = explode("/", $request->input('date'));
                $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                $transaction_list = Transactions::where('payer_id', Auth::user()->id)
                ->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();

                foreach ($transaction_list as $transaction) {
                    $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                    if (!in_array($consultant->id, $consultantIds)) {
                        array_push($consultantIds, $consultant->id);
                        array_push($consultants, $consultant);
                    }
                    $transaction['consultant'] = $consultant;
                    array_push($transactions, $transaction);
                }
            } else {
                $transactions = [];
                $transaction_list = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();

                foreach ($transaction_list as $transaction) {
                    $consultant = Consultant::where('id', $transaction->receiver_id)->with(['profile', 'user'])->first();
                    if (!in_array($consultant->id, $consultantIds)) {
                        array_push($consultantIds, $consultant->id);
                        array_push($consultants, $consultant);
                    }
                    $transaction['consultant'] = $consultant;
                    array_push($transactions, $transaction);
                }
            }
        } else {
            if ($request->type != 'All') {
                if ($request->type == "earn") {
                    $transactions = [];
                    $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                    $earnings = Transactions::where('receiver_id', $consultant->id)->orderBy('created_at', 'desc')->get();
                    foreach ($earnings as $earning) {
                        $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['customer']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['customer']->user->id);
                            array_push($consultants, $earning['customer']);
                        }
                        if (!isset($earning['customer'])) {
                            $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                        }
                        array_push($transactions, $earning);
                    }
                    if (isset($request->name) && $request->name != 'null') {
                        $transactions = [];
                        $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                        $earnings = Transactions::where('receiver_id', $consultant->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['customer']->user->id);
                                array_push($consultants, $earning['customer']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                        if ($request->consultant != 'null') {
                            $transactions = [];
                            $consultant = Consultant::where('id', $request->consultant)->first();
                            $earnings = Transactions::where('receiver_id', $request->consultant)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                            foreach ($earnings as $earning) {
                                $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['customer']->user->id);
                                    array_push($consultants, $earning['customer']);
                                }
                                if (!isset($earning['customer'])) {
                                    $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                    if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $earning['consultant']->user->id);
                                        array_push($consultants, $earning['consultant']);
                                    }
                                }
                                array_push($transactions, $earning);
                            }
                            if ($request->date != 'null') {
                                $date_array = explode("/", $request->input('date'));
                                $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                                $transactions = [];
                                $consultant = Consultant::where('id', $request->consultant)->first();
                                $earnings = Transactions::where('receiver_id', $request->consultant)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                                foreach ($earnings as $earning) {
                                    $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                    if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $earning['customer']->user->id);
                                        array_push($consultants, $earning['customer']);
                                    }
                                    if (!isset($earning['customer'])) {
                                        $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                        if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                            array_push($consultantIds, $earning['consultant']->user->id);
                                            array_push($consultants, $earning['consultant']);
                                        }
                                    }
                                    array_push($transactions, $earning);
                                }
                            }
                        } else if($request->date != 'null') {
                            $transactions = [];
                            $date_array = explode("/", $request->input('date'));
                            $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                            $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                            $earnings = Transactions::where('receiver_id', $consultant->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            foreach ($earnings as $earning) {
                                $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['customer']->user->id);
                                    array_push($consultants, $earning['customer']);
                                }
                                if (!isset($earning['customer'])) {
                                    $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                    if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $earning['consultant']->user->id);
                                        array_push($consultants, $earning['consultant']);
                                    }
                                }
                                array_push($transactions, $earning);
                            }
                        }
                    } else if ($request->consultant != 'null') {
                        $transactions = [];
                        $consultant = Consultant::where('id', $request->consultant)->first();
                        $earnings = Transactions::where('payer_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['customer']->user->id);
                                array_push($consultants, $earning['customer']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                        if ($request->date != 'null') {
                            $transactions = [];
                            $date_array = explode("/", $request->input('date'));
                            $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                            $consultant = Consultant::where('id', $request->consultant)->first();
                            $earnings = Transactions::where('payer_id', $request->consultant)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            foreach ($earnings as $earning) {
                                $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['customer']->user->id);
                                    array_push($consultants, $earning['customer']);
                                }
                                if (!isset($earning['customer'])) {
                                    $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                    if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $earning['consultant']->user->id);
                                        array_push($consultants, $earning['consultant']);
                                    }
                                }
                                array_push($transactions, $earning);
                            }
                        }
                    } else if ($request->date != 'null') {
                        $transactions = [];
                        $date_array = explode("/", $request->input('date'));
                        $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                        $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                        $earnings = Transactions::where('receiver_id', $consultant->id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($consultant->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['customer']->user->id);
                                array_push($consultants, $earning['customer']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($consultant->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                    }
                } else {
                    $transactions = [];
                    $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                    $spendings = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
                    foreach ($spendings as $spend) {
                        $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $spend['consultant']->user->id);
                            array_push($consultants, $spend['consultant']);
                        }
                        array_push($transactions, $transaction);
                    }
                    if (isset($request->name) && $request->name != 'null') {
                        $transactions = [];
                        $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                        $spendings = Transactions::where('payer_id', Auth::user()->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $transaction);
                        }
                        if ($request->consultant != 'null') {
                            $transactions = [];
                            $consultant = Consultant::where('id', $request->consultant)->first();
                            $spendings = Transactions::where('payer_id', $consultant->user_id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                            foreach ($spendings as $spend) {
                                $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                                if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $spend['consultant']->user->id);
                                    array_push($consultants, $spend['consultant']);
                                }
                                array_push($transactions, $transaction);
                            }
                            if ($request->date != 'null') {
                                $date_array = explode("/", $request->input('date'));
                                $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                                $transactions = [];
                                $consultant = Consultant::where('id', $request->consultant)->first();
                                $spendings = Transactions::where('payer_id', $consultant->user_id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                                foreach ($spendings as $spend) {
                                    $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                                    if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $spend['consultant']->user->id);
                                        array_push($consultants, $spend['consultant']);
                                    }
                                    array_push($transactions, $transaction);
                                }
                            }
                        } else if($request->date != 'null') {
                            $transactions = [];
                            $date_array = explode("/", $request->input('date'));
                            $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                            $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                            $spendings = Transactions::where('payer_id', Auth::user()->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            foreach ($spendings as $spend) {
                                $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                                if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $spend['consultant']->user->id);
                                    array_push($consultants, $spend['consultant']);
                                }
                                array_push($transactions, $transaction);
                            }
                        }
                    } else if ($request->consultant != 'null') {
                        $$transactions = [];
                        $consultant = Consultant::where('id', $request->consultant)->first();
                        $spendings = Transactions::where('payer_id', $consultant->user_id)->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $transaction);
                        }
                        if ($request->date != 'null') {
                            $transactions = [];
                            $date_array = explode("/", $request->input('date'));
                            $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                            $consultant = Consultant::where('id', $request->consultant)->first();
                            $spendings = Transactions::where('payer_id', $consultant->user_id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            foreach ($spendings as $spend) {
                                $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                                if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $spend['consultant']->user->id);
                                    array_push($consultants, $spend['consultant']);
                                }
                                array_push($transactions, $transaction);
                            }
                        }
                    } else if ($request->date != 'null') {
                        $transactions = [];
                        $date_array = explode("/", $request->input('date'));
                        $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                        $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                        $spendings = Transactions::where('payer_id', Auth::user()->id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $spend);
                        }
                    }
                }
            } else {
                $transactions = [];
                $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                $spendings = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
                $earnings = Transactions::where('receiver_id', $consultant->id)->orderBy('created_at', 'desc')->get();
                foreach ($spendings as $spend) {
                    $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                    if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                        array_push($consultantIds, $spend['consultant']->user->id);
                        array_push($consultants, $spend['consultant']);
                    }
                    array_push($transactions, $transaction);
                }
                foreach ($earnings as $earning) {
                    $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                    if (!in_array($earning['customer']->user->id, $consultantIds)) {
                        array_push($consultantIds, $earning['customer']->user->id);
                        array_push($consultants, $earning['customer']);
                    }
                    if (!isset($earning['customer'])) {
                        $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['consultant']->user->id);
                            array_push($consultants, $earning['consultant']);
                        }
                    }
                    array_push($transactions, $earning);
                }
                if (isset($request->name) && $request->name != 'null') {
                    $transactions = [];
                    $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                    $spendings = Transactions::where('payer_id', Auth::user()->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                    $earnings = Transactions::where('receiver_id', $consultant->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                    foreach ($spendings as $spend) {
                        $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $spend['consultant']->user->id);
                            array_push($consultants, $spend['consultant']);
                        }
                        array_push($transactions, $transaction);
                    }
                    foreach ($earnings as $earning) {
                        $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['customer']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['customer']->user->id);
                            array_push($consultants, $earning['customer']);
                        }
                        if (!isset($earning['customer'])) {
                            $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                        }
                        array_push($transactions, $earning);
                    }
                    if ($request->consultant != 'null') {
                        $transactions = [];
                        $consultant = Consultant::where('id', $request->consultant)->first();
                        $spendings = Transactions::where('payer_id', $consultant->user_id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                        $earnings = Transactions::where('receiver_id', $request->consultant)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $transaction);
                        }
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['customer']->user->id);
                                array_push($consultants, $earning['customer']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                        if ($request->date != 'null') {
                            $date_array = explode("/", $request->input('date'));
                            $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                            $transactions = [];
                            $consultant = Consultant::where('id', $request->consultant)->first();
                            $spendings = Transactions::where('payer_id', $consultant->user_id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            $earnings = Transactions::where('receiver_id', $request->consultant)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                            foreach ($spendings as $spend) {
                                $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                                if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $spend['consultant']->user->id);
                                    array_push($consultants, $spend['consultant']);
                                }
                                array_push($transactions, $transaction);
                            }
                            foreach ($earnings as $earning) {
                                $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['customer']->user->id);
                                    array_push($consultants, $earning['customer']);
                                }
                                if (!isset($earning['customer'])) {
                                    $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                    if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                        array_push($consultantIds, $earning['consultant']->user->id);
                                        array_push($consultants, $earning['consultant']);
                                    }
                                }
                                array_push($transactions, $earning);
                            }
                        }
                    } else if($request->date != 'null') {
                        $transactions = [];
                        $date_array = explode("/", $request->input('date'));
                        $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                        $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                        $spendings = Transactions::where('payer_id', Auth::user()->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        $earnings = Transactions::where('receiver_id', $consultant->id)->where('transaction_id', 'LIKE', '%'.$request->name.'%')->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                            }
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $transaction);
                        }
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['customer']->user->id);
                                array_push($consultants, $earning['customer']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                    }
                } else if ($request->consultant != 'null') {
                    $$transactions = [];
                    $consultant = Consultant::where('id', $request->consultant)->first();
                    $spendings = Transactions::where('payer_id', $consultant->user_id)->orderBy('created_at', 'desc')->get();
                    $earnings = Transactions::where('receiver_id', $request->consultant)->orderBy('created_at', 'desc')->get();
                    foreach ($spendings as $spend) {
                        $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $spend['consultant']->user->id);
                            array_push($consultants, $spend['consultant']);
                        }
                        array_push($transactions, $transaction);
                    }
                    foreach ($earnings as $earning) {
                        $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['customer']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['customer']->user->id);
                            array_push($consultants, $earning['customer']);
                        }
                        if (!isset($earning['customer'])) {
                            if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                            $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        }
                        array_push($transactions, $earning);
                    }
                    if ($request->date != 'null') {
                        $transactions = [];
                        $date_array = explode("/", $request->input('date'));
                        $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                        $consultant = Consultant::where('id', $request->consultant)->first();
                        $spendings = Transactions::where('payer_id', $consultant->user_id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        $earnings = Transactions::where('receiver_id', $request->consultant)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                        foreach ($spendings as $spend) {
                            $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $spend['consultant']->user->id);
                                array_push($consultants, $spend['consultant']);
                            }
                            array_push($transactions, $transaction);
                        }
                        foreach ($earnings as $earning) {
                            $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['customer']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                            if (!isset($earning['customer'])) {
                                $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                                if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                    array_push($consultantIds, $earning['consultant']->user->id);
                                    array_push($consultants, $earning['consultant']);
                                }
                            }
                            array_push($transactions, $earning);
                        }
                    }
                } else if ($request->date != 'null') {
                    $transactions = [];
                    $date_array = explode("/", $request->input('date'));
                    $date = "$date_array[2]-$date_array[0]-$date_array[1]"." 00:00:00";
                    $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                    $spendings = Transactions::where('payer_id', Auth::user()->id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                    $earnings = Transactions::where('receiver_id', $consultant->id)->where('created_at', '<=', $date)->orderBy('created_at', 'desc')->get();
                    foreach ($spendings as $spend) {
                        $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $spend['consultant']->user->id);
                            array_push($consultants, $spend['consultant']);
                        }
                        array_push($transactions, $spend);
                    }
                    foreach ($earnings as $earning) {
                        $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['customer']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['customer']->user->id);
                            array_push($consultants, $earning['customer']);
                        }
                        if (!isset($earning['customer'])) {
                            $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                        }
                        array_push($transactions, $earning);
                    }
                } else {
                    $transactions = [];
                    $consultant = Consultant::where('user_id', Auth::user()->id)->first();
                    $spendings = Transactions::where('payer_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
                    $earnings = Transactions::where('receiver_id', $consultant->id)->orderBy('created_at', 'desc')->get();
                    foreach ($spendings as $spend) {
                        $spend['consultant'] = Consultant::where('id', $spend->receiver_id)->with(['profile', 'user'])->first();
                        if (!in_array($spend['consultant']->user->id, $consultantIds)) {
                            array_push($consultantIds, $spend['consultant']->user->id);
                            array_push($consultants, $spend['consultant']);
                        }
                        array_push($transactions, $spend);
                    }
                    foreach ($earnings as $earning) {
                        $earning['customer'] = Customer::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                        if (!in_array($earning['customer']->user->id, $consultantIds)) {
                            array_push($consultantIds, $earning['customer']->user->id);
                            array_push($consultants, $earning['customer']);
                        }
                        if (!isset($earning['customer'])) {
                            $earning['consultant'] = Consultant::where('user_id', $earning->payer_id)->with(['profile', 'user'])->first();
                            if (!in_array($earning['consultant']->user->id, $consultantIds)) {
                                array_push($consultantIds, $earning['consultant']->user->id);
                                array_push($consultants, $earning['consultant']);
                            }
                        }
                        array_push($transactions, $earning);
                    }
                }
            }
        }

        $search = [
            'name' => $request->name,
            'consultant' => $request->consultant,
            'date' => $request->date,
            'type' => $request->type
        ];
        return view('member.transaction', [
            'title' => App::getLocale() == 'en' ? 'My Transactions' : 'Transaksjonene mine',
            'description' => '',
            'active' => '3',
            'transactions' => $transactions,
            'consultants' => $consultants,
            'search' => $search
        ]);
    }

    public function profile() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $review_info = null;
        $chart_info = [
            'request_sessions' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ],
            'completed_sessions' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ],
            'response_rates' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ]
        ];
        if (Auth::user()->role == 'consultant') {
            $user_profile = Consultant::where('user_id', Auth::user()->id)->with('user', 'profile', 'company')->first();
            $review_info = Review::where('type', 'CUSTOCON')->where('receiver_id', $user_profile->user_id)->get();
            foreach ($review_info as $review) {
                $customer = Customer::where('user_id', $review->sender_id)->first();
                $review['customer'] = $customer;
            }
            $sessions = Session::where('consultant_id', $user_profile->id)->get();
            $requests = Requests::where('consultant_id', $user_profile->id)->get();
            foreach ($requests as $request) {
                $newDate = date('M d, Y', strtotime($request->created_at));
                $month = explode(" ",$newDate)[0];
                $chart_info['request_sessions'][$month] += 1;
            }
        } else {
            $user_profile = Customer::where('user_id', Auth::user()->id)->with('user', 'profile')->first();
            $sessions = Session::where('customer_id', $user_profile->user_id)->get();
            $review_info = Review::where('type', 'CONTOCUS')->where('receiver_id', $user_profile->user_id)->get();
            foreach ($review_info as $review) {
                $consultant = Consultant::where('user_id', $review->sender_id)->first();
                $review['consultant'] = $consultant;
            }
        }

        foreach ($sessions as $session) {
            $newDate = date('M d, Y', strtotime($session->created_at));
            $month = explode(" ",$newDate)[0];
            $chart_info['completed_sessions'][$month] += 1;
        }

        if (Auth::user()->role == 'consultant') {
            foreach ($chart_info['response_rates'] as $key => $value) {
                if ($chart_info['request_sessions'][$key] != 0) {
                    $chart_info['response_rates'][$key] = $chart_info['completed_sessions'][$key] / $chart_info['request_sessions'][$key] * 100;
                    $chart_info['response_rates'][$key] = round($chart_info['response_rates'][$key], 2);
                }
            }
        }
        $chart_info['no_data'] = count($sessions) > 0 ? false : true;
        $request_type = 'own';
        return view('member.profile', compact('chart_info', 'review_info', 'user_profile', 'request_type'), [
            'title' => App::getLocale() == 'en' ? 'My Profile' : 'Min profil',
            'description' => '',
            'active' => '4'
        ]);
    }

    public function singleProfile($id) {
        $review_info = null;
        $chart_info = [
            'request_sessions' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ],
            'completed_sessions' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ],
            'response_rates' => [ 'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0, 'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0, 'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0 ]
        ];
        if (User::where('account_id', $id)->exists()) {
            $user = User::where('account_id', $id)->first();
        } else {
            $user = User::where('id', $id)->first();
        }
        if (isset($user)) {
            if ($user->role == 'consultant') {
                $user_profile = Consultant::where('user_id', $user->id)->with('user', 'profile')->first();
                $review_info = Review::where('type', 'CUSTOCON')->where('receiver_id', $user->id)->get();
                foreach ($review_info as $review) {
                    $customer = Customer::where('user_id', $review->sender_id)->first();
                    $review['customer'] = $customer;
                }
                $sessions = Session::where('consultant_id', $user_profile->id)->get();
                $requests = Requests::where('consultant_id', $user_profile->id)->get();
                foreach ($requests as $request) {
                    $newDate = date('M d, Y', strtotime($request->created_at));
                    $month = explode(" ",$newDate)[0];
                    $chart_info['request_sessions'][$month] += 1;
                }
            } else {
                $user_profile = Customer::where('user_id', $user->id)->with('user', 'profile')->first();
                $review_info = Review::where('type', 'CONTOCUS')->where('receiver_id', $user->id)->get();
                foreach ($review_info as $review) {
                    $consultant = Consultant::where('user_id', $review->sender_id)->first();
                    $review['consultant'] = $consultant;
                }
                $sessions = Session::where('customer_id', $user_profile->user_id)->get();
            }
            foreach ($sessions as $session) {
                $newDate = date('M d, Y', strtotime($session->created_at));
                $month = explode(" ",$newDate)[0];
                $chart_info['completed_sessions'][$month] += 1;
            }
            if ($user->role == 'consultant') {
                foreach ($chart_info['response_rates'] as $key => $value) {
                    if ($chart_info['request_sessions'][$key] != 0) {
                        $chart_info['response_rates'][$key] = $chart_info['completed_sessions'][$key] / $chart_info['request_sessions'][$key] * 100;
                        $chart_info['response_rates'][$key] = round($chart_info['response_rates'][$key], 2);
                    }
                }
            }
            $chart_info['no_data'] = count($sessions) > 0 ? false : true;
            $request_type = 'other';
            if(Auth::check()){
                return view('member.profile', compact('chart_info', 'review_info', 'user_profile', 'request_type'), [
                    'title' => App::getLocale() == 'en' ? 'My Profile' : 'Min profil',
                    'description' => '',
                    'active' => '4'
                ]);
            } else {
                return view('pages.profile', compact('chart_info', 'review_info', 'user_profile'), [
                    'title' => App::getLocale() == 'en' ? 'Profile' : 'Profil',
                    'description' => ''
                ]);
            }
        } else {
            return redirect('/');
        }
    }

    public function settings() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        if (Auth::user()->role == 'consultant') {
            $consultant = Consultant::where('user_id', Auth::user()->id)->first();
            $education = Education::where('consultant_id', $consultant->id)->get();
        } else {
            $education = null;
        }
        return view('member.settings', compact('education'), [
            'title' => App::getLocale() == 'en' ? 'Settings' : 'Innstillinger',
            'description' => '',
            'active' => '5'
        ]);
    }

    public function memberPrivacy() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $page = Page::where('id','6')->first();
        $data = json_decode($page->page_body);
        return view('member.privacy', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
            'active' => ''
        ]);
    }

    public function memberTermsCustomer() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $page = Page::where('id','5')->first();
        $data = json_decode($page->page_body);
        return view('member.terms_customer', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
            'active' => ''
            ]);
    }

    public function memberTermsProvider() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $page = Page::where('id','9')->first();
        $data = json_decode($page->page_body);
        return view('member.terms_provider', compact('data'), [
            'title' => App::getLocale() == 'en' ? $page->meta_title : $page->no_meta_title,
            'description' => App::getLocale() == 'en' ? $page->meta_description : $page->no_meta_description,
            'active' => ''
            ]);
    }

    public function klarna_checkout(Request $request) {
        $html_snippet = $request->html_snippet;
        return view('member.klarna_checkout', ['html_snippet' => $html_snippet, 'active' => '1']);
    }

    public function klarna_confirmation(Request $request) {
        $sid = $request->sid;
        $merchantId = getenv('KLARNA_MERCHANT_ID') ?: 'PK12126_ebf20e785379';
        $sharedSecret = getenv('KLARNA_SHARED_SECRET') ?: 'eDWpqm3sIuKBi8jq';

        $connector = Connector::create(
            $merchantId,
            $sharedSecret,
            getenv('APP_ENV') === 'local' ?
                ConnectorInterface::EU_TEST_BASE_URL :
                ConnectorInterface::EU_BASE_URL
        );

        try {
            $order = new OrderInStore($connector, $sid);
            $order->acknowledge();

            $id = $request->uid;
            $amount = $request->amount;
            $cur_balance = 0;
            $user = User::where('id', $id)->first();
            $user->balance += $amount;
            $user->payment_method = 'klarna';
            $user->save();
            $cur_balance = $user->balance;
            $currency = $request->currency;
            $charging_transaction = [
                'user_id' => Auth::user()->id,
                'type' => 'Klarna',
                'amount' => $amount,
                'transaction_id' => $sid,
                'status' => 'success'
            ];
            ChargingTransactions::create($charging_transaction);

            $cur_balance = Auth::user()->balance;
            $currency = Auth::user()->currency != null ? Auth::user()->currency : 'NOK';
            $transactions = ChargingTransactions::where('user_id', Auth::user()->id)->get();
            $credits = [
                ["id" => 'card1', 'amount' => $currency == 'NOK'?100:10],
                ["id" => 'card2', 'amount' => $currency == 'NOK'?200:20],
                ["id" => 'card3', 'amount' => $currency == 'NOK'?300:30],
                ["id" => 'card4', 'amount' => $currency == 'NOK'?500:50],
                ["id" => 'card5', 'amount' => $currency == 'NOK'?1000:100],
                ["id" => 'card6', 'amount' => $currency == 'NOK'?2000:200],
                ["id" => 'card7', 'amount' => $currency == 'NOK'?5000:500],
                ["id" => 'card8', 'amount' => 0]
            ];
            $search = [
                'start' => 'null',
                'end' => 'null',
                'type' => 'null'
            ];
            $auth_user = [];
            if (auth()->user()->role == 'consultant') {
                $auth_user = Consultant::where('user_id', auth()->user()->id)->with('profile')->first();
            } else {
                $auth_user = Customer::where('user_id', auth()->user()->id)->with('profile')->first();
            }
            return view('member.wallet', [
                'title' => App::getLocale() == 'en' ? 'My Wallet' : 'Min lommebok',
                'description' => '',
                'active' => '2',
                'transactions' => $transactions,
                'balance' => $cur_balance,
                'currency' => $currency,
                'is_popup' => 'true',
                'amount' => $amount,
                'credits' => $credits,
                'search' => $search,
                'auth_user' => $auth_user
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function forgotPassword(Request $request) {
        return view('auth.forgot-password', ['title' => 'Forgot Password', 'description' => '']);
    }

    public function send_reset_password_request(Request $request) {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $link = URL::route('reset-password', $user->api_token);
            try {
                Mail::to($request->email)->send(new ForgotPassword($link));
            } catch(\Exception $e){
                // Never reached
                return Redirect::to('/password/forgot')->with('alert-error','Enter a valid email address.');
            }
            return Redirect::to('/login')->with('alert-success','Please check your email to reset your password.');
        } else {
            return Redirect::to('/password/forgot')->with('alert-error','You did not sign in correctly or your account is temporaily disabled.');
        }
    }

    public function resetPassword(Request $request) {
        $user = User::where('api_token', $request->code)->first();
        if ($user) {
            return view('auth.reset-password', ['title' => 'Reset Password', 'description' => '', 'id' => $user->id]);
        } else {
            return Redirect::to('/login')->with('alert-success', 'Invalid token');
        }
    }

    public function emailVerification(Request $request) {
        $date = new DateTime('NOW');
        $user = User::where('api_token', $request->code)->first();
        $user->active = 1;
        $user->email_verified_at = $date->format('Y-m-d H:i:s');
        $user->save();
        Mail::to($user->email)->send(new UserRegister($user->first_name, $user->role));
        return Redirect::to('/login')->with('alert-success', 'Welcome to a world of consulting. Your account has been activated. Enjoy!');
    }

    public function becomeConsultant(Request $request) {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->ex_phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'consultant',
            'status' => 'offline',
            'balance' => '0',
            'account_id' => $request->account,
            'api_token' => str_random(60),
            'active' => 2
        ]);

        $profile = Profile::create([
            'birth' => $request->birthday,
            'gender' => $request->gender,
            'street' => $request->street,
            'zip_code' => $request->zip_code,
            'avatar' => $request->profile_avatar,
            'profession' => $request->profession,
            'from' => $request->from,
            'country' => $request->country,
            'region' => $request->region,
            'timezone' => $request->timezone,
            'description' => $request->consultant_introduction
        ]);

        $company = Company::create([
            'company_name' => $request->company_name,
            'organization_number' => $request->organization_number,
            'bank_account' => $request->bank_account,
            "first_name" => $request->cfirst_name,
            "last_name" => $request->clast_name,
            "address" => $request->company_address,
            "zip_code" => $request->czip_code,
            "zip_place" => $request->company_region,
            "country" => $request->company_from
        ]);

        $consultant = Consultant::create([
            'user_id' => $user->id,
            'profile_id' => $profile->id,
            'company_id' => $company->id,
            'chat_contact' => $request->chat_contact,
            'phone_contact' => $request->phone_contact,
            'video_contact' => $request->video_contact,
            'currency' => $request->currency,
            'hourly_rate' => $request->rate
        ]);

        if ($request->education_count > 0) {
            for ($i = 0; $i < $request->education_count; $i++) {
                Education::create([
                    "consultant_id" => $consultant->id,
                    "from" => $request["education{$i}_from"],
                    "to" => $request["education{$i}_to"],
                    "institution" => $request["education{$i}_institution"],
                    "major" => $request["education{$i}_major"],
                    "degree" => $request["education{$i}_degree"],
                    "description" => $request["education{$i}_description"],
                    "diploma" => $request["education{$i}_diploma"]
                ]);
            }
        }
        if ($request->experience_count > 0) {
            for ($i = 0; $i < $request->experience_count; $i++) {
                Experience::create([
                    "consultant_id" => $consultant->id,
                    "from" => $request["experience{$i}_from"],
                    "to" => $request["experience{$i}_to"],
                    "company" => $request["experience{$i}_company"],
                    "position" => $request["experience{$i}_position"],
                    "country" => $request["experience{$i}_country"],
                    "city" => $request["experience{$i}_city"],
                    "description" => $request["experience{$i}_description"]
                ]);
            }
        }
        if ($request->certificate_count > 0) {
            for ($i = 0; $i < $request->certificate_count; $i++) {
                Certificate::create([
                    "consultant_id" => $consultant->id,
                    "date" => $request["certificate{$i}_date"],
                    "name" => $request["certificate{$i}_name"],
                    "institution" => $request["certificate{$i}_institution"],
                    "description" => $request["certificate{$i}_description"],
                    "diploma" => $request["certificate{$i}_diploma"]
                ]);
            }
        }
        Mail::to($user->email)->send(new ConsultantRegisterSuccess());
        return Redirect::to('/login')->with('alert-success', App::getLocale() == 'en' ? 'Thank you for your interest. We will contact you by email once your application is processed.' : 'Takk for din interesse! Vi vil ta kontakt på e-post når søknaden din har blitt prosessert.');
    }

    public function reset_password (Request $request) {
        $user = User::where('id', $request->id)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return redirect('/login')->with('alert-success', App::getLocale() == 'en' ? 'Your password has been successfully reset. You can now login to GotoConsult.' : 'Vennligst sjekk e-posten din for å opprette et nytt passord.');
    }
}
