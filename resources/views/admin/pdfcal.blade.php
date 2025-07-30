<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>
    <h2>{{@$pdfTitle}}  - {{@$courtName}}</h2>
<table>
    <tbody>
      <?php $i =1;?>
        @foreach($court_timeslots as $week =>$weekData)
        <tr>
            <td>Week {{$i}} {{@$week}}</td>
        </tr>
        <tr>
             <td>
             <?php $tempC= 0; $description = ''; $i++;?>
             @foreach($weekData as $weekRes)
             <?php
             $tempC +=  $weekRes['tCount'];
             if(empty($description)){
              $description = !empty($weekRes['timeslotDescription']) ? '<Br>' . $weekRes['timeslotDescription'] : '';
             }

             ?>
             @endforeach
             {{@$tempC.' Free Timeslots'}}  {!! $description !!}
            </td>
        </tr>
     @endforeach
    </tbody>
</table>
</body>
</html>


