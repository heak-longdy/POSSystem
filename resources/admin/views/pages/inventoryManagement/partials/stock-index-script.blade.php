<script lang="ts">
    $(document).ready(function() {
        $("#date").datepicker({
            changeYear: true,
            gotoCurrent: true,
            yearRange: "-1:+1",
            dateFormat: "yy-mm-dd",
        });

        $('.SelectShop').select2({
            placeholder: 'Select Shop',
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
            document.querySelector('.select2-search__field').focus();
        });
    });
</script>
<script lang="ts">
    document.addEventListener('alpine:init', () => {
        Alpine.data('xIndex', () => ({
            init() {},
            verifyDialog(data, typeAction, btn) {
                this.$store.confirmDialog.open({
                    data: {
                        message: `Are you sure want to ${btn} ?`,
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
