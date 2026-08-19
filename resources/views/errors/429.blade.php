@extends('errors.layout')

@section('code', '429')
@section('icon', 'bi-hourglass-split')
@section('theme', 'amber')
@section('title', 'Too Many Requests')
@section('message', "You've made too many requests in a short time. Please wait a moment and try again.")
@section('bn_message', 'অল্প সময়ে অনেকবার চেষ্টা করা হয়েছে। কিছুক্ষণ অপেক্ষা করে আবার চেষ্টা করুন।')