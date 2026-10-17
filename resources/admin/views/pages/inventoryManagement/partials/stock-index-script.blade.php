<script lang="ts">
    $(document).ready(function() {
        $("#date").datepicker({
            changeYear: true,
            gotoCurrent: true,
            yearRange: "-1:+1",
            dateFormat: "yy-mm-dd",
        });
    });
</script>
<script lang="ts">
    document.addEventListener('alpine:init', () => {
        Alpine.data('xIndex', () => ({
            init() {
                this.$nextTick(() => {
                    $(this.$root).find('.SelectShop').filter(function () { return !$(this).data('select2'); }).select2({
                        placeholder: @json(__('stock_in.select_shop')),
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: 'GET',
                            quietMillis: 50,
                            data: function(param) {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return {
                                            text: item?.name ? item.name : '',
                                            id: item.id
                                        };
                                    })
                                };
                            }
                        }
                    }).on('select2:open', () => {
                        window.$select2FocusInputSearch();
                    });
                });
            },
            verifyDialog(data, typeAction, btn) {
                const confirmTemplate = `{{ __('global.dialog.confirm_action', ['action' => '__ACTION__']) }}`;
                const confirmMsg = confirmTemplate.replace('__ACTION__', btn);
                this.$store.confirmDialog.open({
                    data: {
                        message: confirmMsg,
                        btnClose: `{{ __('action_button.cancel') }}`,
                        btnSave: btn,
                        item: data,
                        urlName: `{{ $routeName }}`,
                        typeAction: typeAction,
                        digPosition: "posTop",
                        class: "deleteDialog",
                        width: "18rem"
                    },
                    afterClosed: (result) => {
                        if (result) {
                            reloadData(`{{ url()->full() }}`);
                        }
                    }
                });
            },
        }));
    });
</script>
