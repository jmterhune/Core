<html>

<head id="ctl00">
    <title>
        Mediation Report
    </title>
    <style type="text/css" media="print">
        .noprt {
            display: none;
        }

        p.page {
            page-break-after: always
        }
    </style>
    <style>
        .auto-style1 {
            font-size: 20px;
            font-weight: 300;
        }
    </style>
</head>

<body>
    <form name="form1" method="post" id="form1" style="font-size: 18px; font-family:Arial; margin-left:20px; ">

        <span id="L_errmsg" style="color: #CC0000; font-weight: 700"></span>

        <div style="text-align:center;width:700px; ">

            <span id="L_title" style="font-size: 22px; font-weight:700; ">CASE FOR MEDIATION</span>

        </div>

        <br />
        <div>


            <table style="width:700px; font-size:20px;font-weight:700;">
                <tr>
                    <td>
                        <b>Case No:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                            <span id="c_caseno">{{$case->c_caseno}}</span><br /><br />
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Division:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                            <span id="c_div">{{@$case->judge->name}}</span> <br /><br />
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Plaintiff:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                        <span id="c_pltf_name">{{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['plaintiff', 'petitioner'])->pluck('name')->toArray()) }}</span> <br /><br />

                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Plaintiff Attorney:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                            <!-- //<span id="p_a_name">{{@$case->PltfAttroney->name}}</span><br /><br /> -->
                            <span id="p_a_name">{{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['plaintiff', 'petitioner'])->pluck('attorney_id'))->pluck('name')->toArray()) }}</span> <br /><br />

                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Defendant:<br /><br /> </b>
                    </td>
                    <td>
                        <b>

                            <span id="c_def_name">{{ implode(", ", \App\Models\Party::where('mediation_case_id', $case->id)->where('type', ['defendant', 'respondent'])->pluck('name')->toArray()) }}</span> <br /><br />
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Defendant Attorney:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                            <span id="d_a_name">{{ implode(", ", \App\Models\Attorney::whereIn('id', \App\Models\Party::where('mediation_case_id', $case->id)->whereIn('type', ['defendant', 'respondent'])->pluck('attorney_id'))->pluck('name')->toArray()) }}</span> <br /><br />

                        </b>
                    </td>


                <tr>
                    <td>
                        <b>Type of Case:<br /><br /> </b>
                    </td>
                    <td>
                        <b>
                            <span id="c_type">{{@$case->c_type}}</span><br /><br />
                        </b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b></b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b>Disposition:</b>
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align:top" class="auto-style1">
                        <ol>
                            <li>Stipulation</li>
                            <li>Dismissed</li>
                            <li>Trial Set</li>
                            <li>Arbitration</li>



                        </ol>
                    </td>
                    <td style="vertical-align:top" class="auto-style1">
                        <ol start="5">

                            <li>Settled Prior</li>
                            <li>Stip to Judgment</li>
                            <li>Canceled Due To ________</li>
                            <li>Judgment by Default and Reset</li>

                        </ol>
                    </td>
                </tr>

            </table>

            <div>
                <table id="GridView1_ctl00" cellspacing="0" rules="all" border="1" style="border-collapse:collapse;font-family:Arial;font-size:14px;">
                    <tbody>
                        <tr id="GridView1_ctl01">
                            <th id="GridView1_ctl01_ctl00" scope="col">Mediation Schedule</th>
                            <th id="GridView1_ctl01_ctl01" scope="col">Length</th>
                            <!-- <th id="GridView1_ctl01_ctl02" scope="col">Result</th> -->
                            <th id="GridView1_ctl01_ctl03" scope="col">Subject</th>
                            <th id="GridView1_ctl01_ctl04" scope="col">Mediator</th>
                            <th id="GridView1_ctl01_ctl05" scope="col">Defendent FTA</th>
                            <th id="GridView1_ctl01_ctl06" scope="col">Plaintiff FTA</th>
                        </tr>
                        @foreach($case->events as $event)
                        <tr id="GridView1_ctl02">
                            <td id="GridView1_ctl02_ctl00">{{$event->e_sch_datetime}}</td>
                            <td id="GridView1_ctl02_ctl01" align="right">
                                <span id="GridView1_ctl02_Label1">{{$event->e_sch_length}}</span>
                            </td>
                            <!-- <td id="GridView1_ctl02_ctl04" align="center">2</td> -->
                            <td id="GridView1_ctl02_ctl05">{{$event->e_subject}}</td>
                            <td id="GridView1_ctl02_ctl06">{{$event->medmaster->name}}</td>
                            <td id="GridView1_ctl02_ctl07"></td>
                            <td id="GridView1_ctl02_ctl08"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <div>

            </div>



            <br />
            <span id="Label1" style="font-size: medium; font-weight: 700">Order to approve completed and submitted with agreement: YES__ NO__</span>

        </div>
    </form>
</body>
<script>
    window.print();
</script>

</html>
