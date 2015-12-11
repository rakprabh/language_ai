<div id="tabs">
  <ul>
  	<li id="tab1" ><a href="#tabs-1">Digi-Brain+</a></li>
    <li id="tab2" ><a href="#tabs-2">Word History</a></li>
    <li id="tab3" ><a href="#tabs-3">Question History</a></li>
    <li id="tab4" ><a href="#tabs-4">Response History</a></li>
  </ul>
  <div id="user" >Welcome <?php echo $user_name . "  ";?><a href="<?php echo "index.php/welcome/logout";?>" >Logout</a></div>
  <div id="tabs-1">
  	<!--	<h3>Talk to me (Ver 1.0)</h3>		 
		<div id="notice" >Talk to me Is an AI Program Which Learns the Natural Language like a baby ,Teach it how to respond if it says "I dont know , tell me",<br />It will acknowledge you with "ok".<br />You can also correct its answer. </div>
	-->
	<img height="256" width="256" src="images/brain.png" />
	<p id="level" >Language IQ : <?php echo $level; ?></p>
    <br>		
		<div id="responsediv" ></div>
		<!--
        Enter Captcha <br/><img id="capcha" src="index.php/Welcome/captcha" />
		<input type="text" size="10" id="captcha"  /> -->
		<input type="text" size="100" maxlength="250" id="correction_text" />
		<button id="correct" onclick="correct();" >Correct Me</button><br /><br />
		<input type="text" size="100" maxlength="250" id="query" />
		<button id="talk" onclick="getResponse();" >Talk</button>
  </div>
  <div id="tabs-2">
  	<table id="WordTable" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Word</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
  </div>
  <div id="tabs-3">
    Keyword: <input type="text" id="keyword" >&nbsp;&nbsp;&nbsp;&nbsp;
  	From Date: <input type="text" id="fromdate" readonly>&nbsp;&nbsp;&nbsp;&nbsp;
  	To Date: <input type="text" id="todate" readonly>
  	<button  onclick="updateQuestions();" >Search</button><br /><br /><br /><br />
  	<table id="QuetionsTable" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Question</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
  	
  </div>
  <div id="tabs-4">
  	Keyword: <input type="text" id="rkeyword" >&nbsp;&nbsp;&nbsp;&nbsp;
  	<button  onclick="updateResponsesKey();" >Search</button> <br /> <br />
  	From Date: <input type="text" id="rfromdate" readonly>&nbsp;&nbsp;&nbsp;&nbsp;
  	To Date: <input type="text" id="rtodate" readonly>
  	<button  onclick="updateResponses();" >Search</button><br /><br /><br /><br />
  	<table id="ResponseTable" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Question</th>
                <th>Response</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
  </div>
</div>
<img  id="wait" src="images/loading.gif" />