function getDate(){
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    var dateNow = yyyy + '-' + mm + '-' + dd;
    return dateNow;
}
function getDateAndTime(){
    var dateTimeNow = getDate();
    var today = new Date();
    var hh = String(today.getHours());
    var mm = String(today.getMinutes());
    dateTimeNow = dateTimeNow + 'T' + hh + ':' + mm;
    return dateTimeNow;
}
$(document).ready(function (){ // wird ausgeführt sobald die Seite geladen ist
    var dateNow = getDate();
    $('#newExpirationDate').attr('min', dateNow);
    dateNow = getDateAndTime();
    $('#timeslot_1').attr('min', dateNow);
    $("#liste").hide();
    $("#details").hide();
    loadAppointments();
});

function addNewTimeslot(counter){
    $("#addedTimeslots").append("<div class='input-group'><span class='input-group-text input-group-left-example'>Timeslot " + counter + ":</span><input class='form-control' type='datetime-local' onkeypress='return false' id='timeslot_" + counter + "'></div>")
    var dateTimeNow = getDateAndTime();
    $("input[type=datetime-local]").attr('min', dateTimeNow);
    counter++;
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(' + counter + ')');
}

function loadAppointments(){
    $.ajax({
        type:"GET",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "getAppointmentList", param: ""},
        dataType: "json",
        success: function(response){
            console.log(response);
            $.each(response, function (i, p){
                $("#listitems").append("<li class='list-group-item'><b>" + p['title'] + "</b></li>");
                $("#listitems li:last-child").attr("data-id", p['app_id']);
            });
            $("#listitems").on("click", "li", loadDetails);
        },
        error: function(e){
            $("#error").empty();
            $("#error").hide();
            $("#error").append("<li class='list-group-item' id='error'>Something went wrong :(</li>");
            $("#error").show();
        }
    });
    $("#liste").show();
}

function loadDetails(){
    $("#detailitem").empty();
    $("#partAndComm").empty();
    $("#timeslots").empty();
    // AJAX mit Suche nach id
    $.ajax({
        type:"GET",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "getAppointmentById", param: $(this).attr('data-id')},
        dataType: "json",
        success: function(response){
            console.log(response);
            $("#detailitem").append("<li class='list-group-item'><b>" + response.title + "</b></li>");
            $("#detailitem").append("<li class='list-group-item'>von <u>" + response.creator + "</u></li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.description + "</li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.location + "</li>");
            let counter = 0;
            $.each(response.timeslots, function() {
                $("#timeslots").append("<label class='list-group-item'><input class='form-check-input me-1' type='checkbox' checkbox-id='" + counter + "'>" + response.timeslots[counter].start_datetime + "</label>");
                counter++;
            });
            counter = 0;
            $("#partAndComm").append("<li class='list-group-item'><b>derzeitige Votes</b></li>");
            $.each(response.participants, function() {
                $("#partAndComm").append("<li class='list-group-item'><u>" + response.participants[counter].username + "</u></li>");
                counter++;
            });
            $("#partAndComm").append("<li class='list-group-item'><b>derzeitige Kommentare</b></li>");
            
        },
        error: function(response){
        }
    });
    $("#details").show();
}

function sendNewData(){
    if(!$("#newTitle").val()){
        $("#titleError").remove();
        $("#titleCard").after("<div id='titleError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Titel eingeben um das Appointment zu erstellen' readonly></div>")
        return;
    }
    var newTitle = $("#newTitle").val();
    $("#titleError").remove();
    if(!$("#newCreator").val()){
        $("#creatorError").remove();
        $("#creatorCard").after("<div id='creatorError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Namen eingeben um das Appointment zu erstellen' readonly></div>")
        return;
    }
    var newCreator = $("#newCreator").val();
    $("#creatorError").remove();
    if(!$("#newDescription").val()){
        $("#descriptionError").remove();
        $("#descriptionCard").after("<div id='descriptionError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen eine Beschreibung eingeben um das Appointment zu erstellen' readonly></div>")
        return;
    }
    var newDescription = $("#newDescription").val();
    $("#descriptionError").remove();
    if(!$("#newLocation").val()){
        $("#locationError").remove();
        $("#locationCard").after("<div id='locationError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Ort eingeben um das Appointment zu erstellen' readonly></div>")
        return;
    }
    var newLocation = $("#newLocation").val();
    $("#locationError").remove();
    if(!$("#newExpirationDate").val()){
        $("#expirationError").remove();
        $("#expirationCard").after("<div id='expirationError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen ein Ablaufdatum eingeben um das Appointment zu erstellen' readonly></div>")
        return;
    }
    var newExpirationDate = $("#newExpirationDate").val();
    $("#expirationError").remove();
    var newTimeslots = [];
    $("input[type=datetime-local]").each(function(){
        newTimeslots.push($(this).val());
    });
    var newData = {title: newTitle, creator: newCreator, description: newDescription, location: newLocation, expirationDate: newExpirationDate, timeslots: newTimeslots}
    newData = JSON.stringify(newData);
    $("input").val("");
    console.log(newData);
    /*$.ajax({
        type: "POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "newAppointment", param: newData}
    }); */
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(2)');
    $("#detailitem").empty();
    $("#timeslots").empty();
    $("#partAndComm").empty();
    $("#addedTimeslots").empty();
    $("#listitems").empty();
    loadAppointments();
}

function addVotes(){
    // AJAX call zu addVotes
    if(!$("#partName").val()){
        $("#nameError").remove();
        $("#name").after("<div id='nameError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Namen eingeben um abzustimmen' readonly></div>")
        return;
    }
    var partName = $("#partName").val();
    var partComm = "";
    if($("#partComm").val()){
        partComm = $("#partComm").val();
    }
    var votesArray = [];
    $('input[type=checkbox]').each(function(){
        if($(this).is(':checked')){
            votesArray.push($(this).attr('checkbox-id'));
        }
    });
    var newVotes = {name: partName, timeslotIds: votesArray, comment: partComm}
    newVotes = JSON.stringify(newVotes);
    $("input").val("");
    console.log(newVotes);
    $("#detailitem").empty();
    $("#timeslots").empty();
    $("#partAndComm").empty();
    $("#nameError").remove();
    $("#details").hide();
}