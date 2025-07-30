
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Hearing confirmation</title>
</head>
<body>
<pre>
	***************  Florida 18th Judicial Circuit  ***************
	                BREVARD FORECLOSURE HRGS
	                BREVARD FORECLOSURE JUDGE
	***************************************************************
</pre>
<div>
     @isset($Hearingdata['custom_email_body'])
          {{$Hearingdata['custom_email_body']}}
     @else
	<div>Hearing Confirmation on <span>{{$Hearingdata['convert_date']}}</span> at <span>{{$Hearingdata['time']}}</span>for 10 minutes </div>

     <div>Case # : <span>{{$Hearingdata['case']}}</span></div>
     <div>Motion : <span>{{$Hearingdata['motion']}}</span></div>
     <div>Attorney : <span>{{$Hearingdata['attorney']}}</span></div>
     <div>Plaintiff : <span>{{$Hearingdata['plaintiff']}}</span></div>
     <div>Opposing Attorney : <span>{{$Hearingdata['opp_attorney']}}</span></div>
     <div>Defendant : <span>{{$Hearingdata['defendant']}}</span> </div>
     <div>Confirmation # :<span> {{$Hearingdata['confirmation']}}</span></div>
     <div>Scheduling Reason : <span>plaintiff received discovery responses</span></div>
     @endif
     If you want to subcribe for Remainders. Please click on this Link.
     <a href="{{ env('APP_URL')  }}/event-reminder-link/{{$Hearingdata['eventId']}}/{{$Hearingdata['toEmail']}}">Click Here</a>
     <br>
     <div>*** Please do not reply to this email ***</div>
</div>
</body>
</html>
