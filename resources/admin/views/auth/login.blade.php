@extends('admin::index')
@section('index')
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            background: #fff !important;
            overflow: hidden;
            height: 100vh;
        }

        .login-box {
            background-color: #fff;
            /* padding: 50px; */
            width: 100%;
            /* box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); */
            text-align: center;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-left {
            width: 33rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 25px;
            /* height: fit-content;
                                                                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); */
            padding: 60px 0;
        }

        .loginForm {
            padding: 50px 60px 0;
            width: 85%;

        }

        .inputGp {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 20px;
        }

        .inputGp>label {
            text-align: right;
            float: left;
            font-size: 14px;
            color: black;
            padding: 0 12px 0 0;
            box-sizing: border-box;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            grid-gap: 7px;
            pointer-events: none;
        }

        .inputGp>label>svg {
            width: 20px;
            height: 20px;
        }

        .inputGp>.groupInput {
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
        }

        .inputGp>.groupInput>input {
            padding: 0 40px 0 20px !important;
        }

        .inputGp>.groupInput>.groupIcon {
            position: absolute;
            right: 10px;
            cursor: pointer;
        }

        .inputGp>.groupInput>.groupIcon>svg {
            width: 23px;
            height: 23px;
        }

        .inputGp>input,
        .inputGp>.groupInput>input {
            background: #F5F5F5;
            border: 1px solid #F5F5F5;
            border-radius: 15px;
            box-sizing: border-box;
            color: #231f20;
            display: inline-block;
            font-size: 14px;
            height: 40px;
            line-height: 1;
            outline: 0;
            padding: 0 20px;
            transition: border-color 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            width: 100%;
            box-sizing: border-box;
        }

        .error-input {
            border: 1px solid #ff000061 !important;
        }

        .loginFooter {
            margin-top: 35px;
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .loginFooter>button {
            justify-content: center;
            border-radius: 15px !important;
            margin: 0 auto;
            height: 42px;
            width: 100%;
            display: flex;
            align-items: center;
            border: none;
            color: #231f20 !important;
            font-size: 14px;
            cursor: pointer;
            padding: 0 20px;
        }

        .loginFooter>button>svg {
            width: 20px;
            height: 20px;
            margin-right: 7px;
            /* fill: #231f20d1; */
        }

        .loginFooter>button:hover {
            background-color: #3C91E6;
            color: #fff !important;
        }

        /* .loginFooter>button:hover>svg {
                                fill: #fff;
                            } */

        .logo {
            flex: 0.7;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo>img {
            width: 70%;
            height: 70%;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            margin: 0;
        }
    </style>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="{{ asset('admin-public/logo/login-bg.svg') }}" alt="System Logo">
            </div>
            <div class="login-left">
                <h2>Welcome to sign in system</h2>
                <p>Sign in by entering the information below</p>
                <form class="loginForm" id="login-form" action="{!! route('admin-login-post') !!}" method="post">
                    @csrf
                    <input type="hidden" name="returnUrl" value={{ request()->returnUrl }}>
                    <div class="inputGp">
                        <label>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>

                            <span>Email</span>
                        </label>
                        <input type="text" placeholder="Enter Email ..." name="email" autocomplete="off"
                            error-message="@lang('auth.sign-in.form.username.error')" />
                    </div>
                    <div class="inputGp">
                        <label>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>

                            <span>Password</span>
                        </label>
                        <div class="groupInput" x-data={show:false}>
                            <input name="password" x-bind:type="!show ? 'password' : 'text'" placeholder="@lang('auth.sign-in.form.password.placeholder')"
                                error-message="@lang('auth.sign-in.form.password.error')" autocomplete="off">
                            <div class="groupIcon" @click="show = !show">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6" x-show="!show">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6" x-show="show">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    @if (Session::has('status'))
                        <p class="q-label-error">
                            @lang('auth.sign-in.form.error_login')
                        </p>
                    @endif
                    <div class="loginFooter">
                        <button color="primary" class="btn-submit-form" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                              </svg>
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        $(document).ready(function() {
            let validate = {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minLength: 6,
                    maxLength: 20
                }
            };
            $validator("#login-form", validate, {
                inputClass: "error-input",
                messageClass: "error",
                showMessage: false
            });
        });
    </script>
@stop
