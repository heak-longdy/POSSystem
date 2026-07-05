<style>
    /* body {
        font-family: Arial, sans-serif;
        background-color: #2E2E2E;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    } */

    .command-palette {
        width: 350px;
        /* background-color: #3E3E3E;
        border-radius: 10px; */
        /* padding: 20px; */
        /* box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5); */
    }

    .search-input {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 10px;
        color: #4f4f4f;
        font-size: 15px;
        border: 1px solid rgba(152, 152, 152, 0.2);
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.1)
    }

    .section {
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 14px;
        color: #888;
        margin-bottom: 10px;
    }

    .option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
        /* background-color: #4E4E4E; */
        /* margin-bottom: 10px; */
    }

    .option.selected {
        background-color: #6E6E6E;
        color: #fff;
    }

    .option .left {
        display: flex;
        align-items: center;
    }

    .option .label {
        margin-left: 10px;
    }

    .option .title {
        font-size: 14px;
        /* color: white; */
    }

    .option .subtitle {
        font-size: 12px;
        color: #aaa;
    }

    .option .right {
        font-size: 12px;
        color: #aaa;
    }

    .shortcut {
        background-color: #5E5E5E;
        padding: 4px 8px;
        border-radius: 5px;
    }

    .icon-folder,
    .icon-document,
    .icon-profile,
    .icon-team,
    .icon-invite,
    .icon-project,
    .icon-support {
        width: 20px;
        height: 20px;
        background-color: #888;
        border-radius: 5px;
    }

    .icon-invite {
        background-color: unset !important;
        width: auto;
        height: auto;
        display: flex;
        justify-content: center;
        align-content: center;
    }

    .icon-invite i {
        font-size: 25px;
    }

    /* Customize icons here with background images if needed */
</style>
<template x-data="{}" x-if="$store.componentSearchMenu.active">
    <div class="dialog" x-data="xSearchMenu" x-bind:style="{ zIndex: $store.libs.getLastIndex() + 1 }"
        style="padding-top: 25px;align-items: flex-start;">
        <div class="dialog-container">
            <div class="select-option" id="select-option">
                <div class="select-option-header">
                    <h3 x-text="options?.title"></h3>
                    <button x-show="options?.allow_close" style="display: none" class="btn-close" @click="close(false)">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="select-option-body" style="padding: 25px 0 0;">

                    <div class="command-palette">
                        <div style="display: flex;align-items: center;position: relative;margin-bottom: 20px;">
                            <input type="text" placeholder="Type a command or search" class="search-input">
                            <i class='bx bx-command' style="position: absolute;right: 11px;font-size: 18px;"></i>
                        </div>
                        <div class="section recent">
                            {{-- <div class="section-title">Recent</div> --}}
                            <template x-for="item in data">
                                <template x-if="item.type =='single'">
                                    <div class="option">
                                        <div class="left">
                                            <div class="icon-invite"><i class='bx ' :class="item.icon"></i></div>
                                            <div class="label">
                                                <div class="title" x-text="item?.name?.en">Marketing site redesign
                                                </div>
                                                <div class="subtitle" x-text="item?.name?.en">Project by
                                                    Olivia Rhye in Notion migration</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>

                            {{-- <div class="option selected">
                                <div class="left">
                                    <div class="icon-invite"><i class='bx bxs-file'></i></div>
                                    <div class="label">
                                        <div class="title">New document</div>
                                        <div class="subtitle">Create a new blank document</div>
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="shortcut">⌘N</div>
                                </div>
                            </div> --}}
                        </div>
                        <template x-for="item in data">
                            <template x-if="item.type =='dropdown-multiple'">
                                <div class="section common" style="margin-bottom: 0;">
                                    <div class="section-title" x-text="item?.label">Common actions</div>
                                    {{-- <template x-if="item.type =='single'">
                                    <div class="option">
                                        <div class="left">
                                            <div class="icon-invite"><i class='bx bx-badge-check'></i></div>
                                            <div class="label">
                                                <div class="title">My profile</div>
                                                <div class="subtitle">View and edit your personal profile</div>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="shortcut">⌘K → P</div>
                                        </div>
                                    </div>
                                </template> --}}
                                    <template x-for="chItem in item.listMenu">
                                        <div class="option">
                                            <div class="left">
                                                {{-- <div class="icon-invite"><i class='bx bx-cast'></i></div> --}}
                                                <div class="icon-invite"><i class='bx ' :class="chItem.icon"></i></div>
                                                <div class="label">
                                                    <div class="title" x-text="chItem?.name?.en">Marketing site redesign
                                                    </div>
                                                    <div class="subtitle" x-text="chItem?.name?.en">Project
                                                        by
                                                        Olivia Rhye in Notion migration</div>
                                                </div>
                                            </div>
                                            <div class="right">
                                                {{-- <div class="shortcut">⌘K → T</div> --}}
                                            </div>
                                        </div>
                                    </template>

                                    {{-- <div class="option">
                                        <div class="left">
                                            <div class="icon-invite"><i class='bx bx-badge-check'></i></div>
                                            <div class="label">
                                                <div class="title">Invite colleagues</div>
                                                <div class="subtitle">Collaborate with your team on projects</div>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="shortcut">⌘I</div>
                                        </div>
                                    </div>

                                    <div class="option">
                                        <div class="left">
                                            <div class="icon-invite"><i class='bx bxl-whatsapp'></i></div>
                                            <div class="label">
                                                <div class="title">Create new project</div>
                                                <div class="subtitle">Create a new blank project</div>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="shortcut">⌘P</div>
                                        </div>
                                    </div>

                                    <div class="option">
                                        <div class="left">
                                            <div class="icon-invite"><i class='bx bxs-key'></i></div>
                                            <div class="label">
                                                <div class="title">Support</div>
                                                <div class="subtitle">Our team is here to help if you get stuck</div>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="shortcut">⌘H</div>
                                        </div>
                                    </div> --}}
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
                <div class="select-option-footer">
                    <template x-if="options.multiple">
                        <button type="button" @click="onClose(selected)"
                            x-bind:disabled="!selected || selected.length == 0">
                            Save (<span x-text="selected?.length || 0"></span>)
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <script>
            Alpine.data('xSearchMenu', () => ({
                data: null,
                loading: true,
                options: null,
                selected: [],
                init() {
                    this.options = Alpine.store('componentSearchMenu').options;
                    this.data = this.options.data;
                    this.selected = this.options.selected;
                    Alpine.store('animate').enter(this.$root.children[0], () => {
                        feather.replace();
                        this.onReady();
                    });
                },
                onReady() {
                    this.$store.componentSearchMenu.options.onReady((data) => {
                        if (!data) return;
                        this.loading = false;
                        this.data = data;
                        console.log("menfslfjsjf", this.data);
                    });
                },
                onInput(e) {
                    this.data = [];
                    this.loading = true;
                    this.$store.componentSearchMenu.options.onSearch(e.target.value, (data) => {
                        if (!data) return;
                        this.loading = false;
                        this.data = data;
                    });
                },
                onSelect(data) {
                    if (this.options.multiple) {
                        if (this.isSelected(data)) {
                            this.selected = this.selected.filter(item => item._id !== data._id);
                        } else {
                            this.selected.push(data);
                        }
                    } else {
                        this.onClose(data);
                    }
                },
                isSelected(data, call_back) {
                    return this.selected?.find(item => item._id == data._id) ? call_back ?? true : false;
                },
                selectedIndex(data) {
                    return this.selected.findIndex(item => item._id == data._id) + 1;
                },
                onClose(data = null) {
                    if (typeof this.$store.componentSearchMenu.options.beforeClose === 'undefined') {
                        this.close(data);
                        return;
                    }
                    this.$store.componentSearchMenu.options.beforeClose(data, (close) => {
                        if (close) {
                            this.close(data);
                        }
                    });
                },
                close(data = null) {
                    Alpine
                        .store('animate')
                        .leave(this.$root.children[0], () => {
                            this.$store.componentSearchMenu.active = false;
                            this.$store.componentSearchMenu.options.afterClose(data);
                        });
                }
            }));
        </script>
    </div>
</template>
<script>
    Alpine.store('componentSearchMenu', {
        active: false,
        options: {
            data: null,
            selected: null,
            multiple: false,
            title: 'Choose an option',
            placeholder: 'Type to search...',
            allow_close: true,
            onReady: () => {},
            onSearch: () => {},
            // beforeClose: () => {},
            afterClose: () => {}
        }
    });
    window.SearchMenu = (options) => {
        Alpine.store('componentSearchMenu', {
            active: true,
            options: {
                ...Alpine.store('componentSearchMenu').options,
                ...options
            }
        });
    };
</script>
