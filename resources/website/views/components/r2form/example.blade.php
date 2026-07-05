<x-r2input :label="__('package.form.create.title')" name="name" :value="request('id') ? $data->name : 0"
    placeholder="Enter name" />
<x-r2textarea label="Description" name="des" :value="request('id') ? $data->des : null" placeholder="Enter price"
    rows="5" />
<x-r2row row="2">
    <x-r2input />
    <x-r2select />
</x-r2row>
<x-r2select label="Status" name="status" :options="json_encode($status)" valueField="value" nameField="name"
    :selected="request('id') ?  $data->status : 1" />
<x-r2photo label="Thumbnail" name="thumbnail" :value="request('id') ? $data->thumbnail ?? null : null"
    :path="request('id') ? asset('uploads/package/' . $data->thumbnail) : null" />
