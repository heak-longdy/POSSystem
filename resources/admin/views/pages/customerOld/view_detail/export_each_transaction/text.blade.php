<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Serif+Khmer:wght@100;200&display=swap');
  @font-face{
    
    font-family:'Noto Sans Khmer';
    src: url("../fonts/NotoSansKhmer-VariableFont_wdth,wght.ttf")
  }.UR{
    font:normal :12px/20px Noto Sans Khmer;
  }
  .HI{
    font:normal :12px/20px Noto Sans Khmer;
  }
</style>

<table class="export_table" id="export_table">
    <thead>
        {{-- <tr>
            <th colspan="6" style="font-size: 30px;height:20px;text-align: center">LIST INCOME</th>
        </tr> --}}
        <tr>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">លេខរៀង</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">កាលបរិច្ឆេទ</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">រូបិយប័ណ្ណ</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">ចំនួន</th>
            <th style="background-color: #50246f;color:white;width: 350px;text-align: center;font-family:'!Khmer OS Battambang'">កត់ចំណាំ​(សង្ខេប)</th>
            <th style="background-color: #50246f;color:white;width: 350px;text-align: center;font-family:'!Khmer OS Battambang'">កត់ចំណាំ(លម្អិត)</th>
        </tr>
        <tr>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">No</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">Date</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">Currency</th>
            <th style="background-color: #50246f;color:white;width: 100px;text-align: center;font-family:'!Khmer OS Battambang'">Amount</th>
            <th style="background-color: #50246f;color:white;width: 200px;text-align: center;font-family:'!Khmer OS Battambang'">Remark(Summary)</th>
            <th style="background-color: #50246f;color:white;width:200px;text-align: center;font-family:'!Khmer OS Battambang'">Description(Detail)</th>
        </tr>
    </thead>
    <tbody>
        {{-- @foreach ($item as $data) --}}
        <tr>
            <td style="text-align: center;border:1px solid #50246f;">1</td>
            <td style="text-align: center;border:1px solid #50246f;">{!! isset($data->revenue_date)? $data->revenue_date : '' !!}</td>
            <td style="text-align: center;border:1px solid #50246f;">USD</td>
            <td style="text-align: center;border:1px solid #50246f;">{!! isset($data->amount_usd)? $data->amount_usd : '' !!}</td>
            <td style="text-align: center;border:1px solid #50246f;">{{!! isset($data->remark) ? $data->remark : ''}}</td>
            <td style="text-align: center;border:1px solid #50246f;">{{!! isset($data->des) ? $data->des : ''}}</td>
        </tr>
        {{-- @endforeach --}}
    </tbody>
</table>
