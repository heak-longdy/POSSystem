window.$autoGridFill = function (parent_class, box_class, _item, _gap) {
    const container = $(parent_class);
    container.each(function () {
        const parent = $(this);
        parent.css({'position': 'relative'});
        let box = $(this).find(box_class);
        let item = _item || 2;
        let gap = _gap || 20;
        let boxWidth = (100 / item / 100) * parent.width() - gap / 2;
        _Height = function (index) {
            let result = 0;
            if (index >= item) {
                for (let i = item; i < index + 1; ) {
                    result +=
                        i > 1
                            ? box[index - i].clientHeight + gap
                            : box[index - i].clientHeight;
                    i += item;
                }
            }
            return result;
        };
        _Width = function (index, item_no) {
            let result = 0;
            if (index < item) {
                result += index * boxWidth;
            } else {
                result = (index % item) * boxWidth;
            }
            return item_no > 0 ? result : result + gap;
        };
        let Arr = [];
        let i = 0;
        box.each(function (index) {
            console.log(index)
            if (Arr[i] == undefined) {
                Arr[i] = 0;
            }
            Arr[i] += index > 1 ? this.clientHeight + gap : this.clientHeight;
            if (i < item - 1) {
                i++;
            } else {
                i = 0;
            }
            let max = Arr.reduce(function (a, b) {
                return Math.max(a, b);
            });
            parent.height(max);
            const size = {
                height: _Height(index) + "px",
                width: _Width(index, i) + "px",
                item: boxWidth + "px",
            };
            this.style.top = size.height;
            this.style.left = size.width;
            this.style.width = size.item;
            this.style.position = "absolute";
        });
    });
};
