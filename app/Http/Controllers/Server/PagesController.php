<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App;
use DateTime;
use App\User;
use App\Models\Categories;
use App\Models\Consultant;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\Page;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Certificate;
use App\Models\Transactions;
class PagesController extends Controller
{
    public function __construct () {
        if(!Auth::check()){
            return redirect('/');
        }
    }
    public function adminDashboard() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $customers = Customer::count();
        $consultants = Consultant::count();
        $start_date = new DateTime('first day of this month');
        $start_time = clone $start_date->setTime(0, 0, 0);
        $end_date = new DateTime('last day of this month');
        $end_time = $end_date->setTime(23, 59, 59);
        
        $earning = 0;
        $transaction_list = Transactions::where('created_at', '>=', $start_time)->where('created_at', '<=', $end_time)->get();
        foreach ($transaction_list as $transaction) {
           $earning += $transaction->amount;
        }
        $search = [
            "start" => 'null',
            "end" => 'null'
        ];
        return view('admin.dashboard', ['active' => '0', 'customers' => $customers, 'consultants' => $consultants, 'search' => $search, 'earning' => $earning]);
    }
    public function adminDashboardSearch(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $startDate_array = explode("/", $request->input('start'));
        $startDate = "$startDate_array[2]-$startDate_array[0]-$startDate_array[1]"." 00:00:00";
        $endDate_array = explode("/", $request->input('end')); 
        $endDate = "$endDate_array[2]-$endDate_array[0]-$endDate_array[1]"." 23:59:59";
        $customers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $consultants = Consultant::whereBetween('created_at', [$startDate, $endDate])->count();
        $earning = 0;
        $transaction_list = Transactions::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->get();
        foreach ($transaction_list as $transaction) {
           $earning += $transaction->amount;
        }
        $search = [
            "start" => $request->start,
            "end" => $request->end
        ];
        return view('admin.dashboard', ['active' => '0', 'customers' => $customers, 'consultants' => $consultants, "search" => $search, 'earning' => $earning]);
    }
    //PAGES
    public function pages () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $pages = Page::all();
        return view('admin.pages', compact('pages'), ['active' => '4']);
    }
    public function createPage() {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        return view('admin.create_page', ['active' => '4']);
    }
    public function editPage(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $page = Page::where('id', $request->id)->first();
        $page_body = json_decode($page->page_body);
        if ($request->id == 6) {
            return view('admin.edit_privacy', compact('page', 'page_body'), ['active' => '4']);
        } else if ($request->id == 5) {
            return view('admin.edit_terms_customer', compact('page', 'page_body'), ['active' => '4']);
        } else if ($request->id == 9) {
            return view('admin.edit_terms_provider', compact('page', 'page_body'), ['active' => '4']);
        } else {
            return view('admin.edit_page', compact('page', 'page_body'), ['active' => '4']);
        }
    }
    //CATEGORIES
    public function categories () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $categories = Categories::all();
        return view('admin.categories', compact('categories'), ['active' => '3']);
    }
    public function createCategory () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        return view('admin.create_category', ['active' => '3']);
    }
    public function editCategory (Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $category = Categories::where('id', $request->id)->first();
        return view('admin.edit_category', compact('category'), ['active' => '3']);
    }
    //CUSTOMERS
    public function customers () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $customers = Customer::with('user', 'profile')->get();
        return view('admin.customers', compact('customers'), ['active' => '1']);
    }
    public function createCustomer () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $categories = Categories::all();
        return view('admin.create_customer', compact('categories'), ['active' => '1']);
    }
    public function editCustomer (Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $user = User::where('id', $request->id)->first();
        $customer = Customer::where('user_id', $request->id)->with('user', 'profile')->first();
        $categories = Categories::all();
        return view('admin.edit_customer', compact('customer', 'user', 'categories'), ['active' => '1']);
    }
    //CONSULTANTS
    public function consultants () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $consultants = Consultant::with('user', 'profile')->get();
        return view('admin.consultants', compact('consultants'), ['active' => '2']);
    }
    public function createConsultant () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $categories = Categories::all();
        $count = Categories::count();
        return view('admin.create_consultant', compact('categories', 'count'), ['active' => '2']);
    }
    public function editConsultant (Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $consultant = Consultant::where('user_id', $request->id)->with('profile', 'user', 'company')->first();
        $educations = Education::where('consultant_id', $consultant->id)->get();
        $experiences = Experience::where('consultant_id', $consultant->id)->get();
        $certificates = Certificate::where('consultant_id', $consultant->id)->get();
        $categories = Categories::all();
        return view('admin.edit_consultant', compact('consultant', 'categories', 'educations', 'experiences', 'certificates'), ['active' => '2']);
    }
    //SETTING
    public function settting () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        return view('admin.settings', ['active' => '6']);
    }

    public function adminTransaction () {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $metaTitle = App::getLocale() == 'en' ? 'My Transactions' : 'Mine transaksjoner';
        $metaDescription = '';

        $start_date = new DateTime('first day of this month');
        $start_time = clone $start_date->setTime(0, 0, 0);
        $end_date = new DateTime('last day of this month');
        $end_time = $end_date->setTime(23, 59, 59);

        $transactions = [];
        $transaction_list = Transactions::where('created_at', '>=', $start_time)->where('created_at', '<=', $end_time)->get();
        foreach ($transaction_list as $transaction) {
            $user = User::where('id', $transaction->payer_id)->first();
            if ($transaction->type == 'CUSTOCON') {
                $payer = Customer::where('user_id', $user->id)->with('profile')->first();
                $receiver = Consultant::where('id', $transaction->receiver_id)->with('profile', 'user')->first();
            } else {
                $payer = Consultant::where('user_id', $user->id)->with('profile')->first();
                $receiver = Consultant::where('id', $transaction->receiver_id)->with('profile', 'user')->first();
            }
            $transaction['payer_img'] = $payer->profile->avatar;
            $transaction['payer_name'] = $user->first_name." ".$user->last_name;
            $transaction['receiver_img'] = $receiver->profile->avatar;
            $transaction['receiver_name'] = $receiver->user->first_name." ".$receiver->user->last_name;
            array_push($transactions, $transaction);
        }
        $consultants = Consultant::with('user')->get();
        $search = [
            'consultant' => 'All',
            'date' => date('M, Y')
        ];
        return view('admin.transaction', compact('transactions', 'consultants', 'search'), ['active' => '5', 'title' => $metaTitle,'description' => $metaDescription]);
    }
    public function adminTransactionSearch(Request $request) {
        if(!Auth::check()){
            return App::getLocale() == 'en' ? redirect('/') : redirect('/no');
        }
        $metaTitle = App::getLocale() == 'en' ? 'My Transactions' : 'Mine transaksjoner';
        $metaDescription = '';
        
        $date_str = explode('/', $request->date);
        $start_date = date('Y-m-d', strtotime($date_str[1]."-".$date_str[0]."-"."01"));
        $end_date = date('Y-m-t', strtotime($date_str[1]."-".$date_str[0]."-"."01"));
        $search_date = date('M, Y', strtotime($date_str[1]."-".$date_str[0]."-"."01"));
        
        $transactions = [];
        $transaction_list = Transactions::where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date)->get();
        if ($request->consultant != 'All') {
            $consultant = Consultant::where('id', $request->consultant)->with('profile', 'user')->first();
            $transaction_list = Transactions::where('payer_id', $consultant->user_id)
                                ->orWhere('receiver_id', $request->consultant)
                                ->where('created_at', '>=', $start_date)
                                ->where('created_at', '<=', $end_date)->get();
        }
        foreach ($transaction_list as $transaction) {
            $user = User::where('id', $transaction->payer_id)->first();
            if ($transaction->type == 'CUSTOCON') {
                $payer = Customer::where('user_id', $user->id)->with('profile')->first();
                $receiver = Consultant::where('id', $transaction->receiver_id)->with('profile', 'user')->first();
            } else {
                $payer = Consultant::where('user_id', $user->id)->with('profile')->first();
                $receiver = Consultant::where('id', $transaction->receiver_id)->with('profile', 'user')->first();
            }
            $transaction['payer_img'] = $payer->profile->avatar;
            $transaction['payer_name'] = $user->first_name." ".$user->last_name;
            $transaction['receiver_img'] = $receiver->profile->avatar;
            $transaction['receiver_name'] = $receiver->user->first_name." ".$receiver->user->last_name;
            array_push($transactions, $transaction);
        }
        $consultants = Consultant::with('user')->get();
        $search = [
            'consultant' => $request->consultant,
            'date' => $search_date
        ];
        return view('admin.transaction', compact('transactions', 'consultants', 'search'), ['active' => '5', 'title' => $metaTitle,'description' => $metaDescription]);
    }
}
