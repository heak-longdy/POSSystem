<x-admin::r2form.input :label="__('package.form.create.title')" name="name" :value="request('id') ? $data->name : 0"
    placeholder="Enter name" />
<x-admin::r2form.textarea label="Description" name="des" :value="request('id') ? $data->des : null" placeholder="Enter price"
    rows="5" />
<x-admin::r2form.row row="2">
    <x-admin::r2form.input />
    <x-admin::r2form.select />
</x-admin::r2form.row>
<x-admin::r2form.select label="Status" name="status" :options="json_encode($status)" valueField="value" nameField="name"
    :selected="request('id') ?  $data->status : 1" />
<x-admin::r2form.photo label="Thumbnail" name="thumbnail" :value="request('id') ? $data->thumbnail ?? null : null"
    :path="request('id') ? asset('uploads/package/' . $data->thumbnail) : null" />
