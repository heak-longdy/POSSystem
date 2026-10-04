@extends('admin::index')
@section('index')
    <div class="container">
        <div class="container-wrapper">
            <div id="sidebar" class="sidebar" x-cloak>
                <script>
                    if (localStorage.getItem("menu") === "0") {
                        document.getElementById("sidebar").classList.add("hide");
                    }
                </script>
                @include('admin::shared.sidebar')
            </div>
            
            <div class="content" id="content" x-data={} >
                @include('admin::shared.workspace-tab-bar')
                
                @include('admin::file-manager.popup')
                @include('admin::components.verify')
                @include('admin::components.select-option')
                @include('admin::components.logout')
                @include('admin::components.search_menu')

                <div id="workspace-viewport" class="workspace-viewport" style="position: relative; width: 100%; flex: 1; display: flex; flex-direction: column;">
                    <div id="tab-pane-initial" class="workspace-tab-pane active" style="width: 100%; flex: 1; display: flex; flex-direction: column;">
                        @yield('layout')
                    </div>
                </div>

                <div id="jsScroll" class="scroll">
                    <i class='bx bx-up-arrow-alt' ></i>
                </div>      
            </div>
        </div>
    </div>
@stop