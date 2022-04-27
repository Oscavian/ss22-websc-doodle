function getDate(){ // findet das aktuelle Datum heraus
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    var dateNow = yyyy + '-' + mm + '-' + dd;
    return dateNow;
}
function getDateAndTime(){ // findet zusätzlich die aktuelle Zeit heraus
    var dateTimeNow = getDate();
    var today = new Date();
    var hh = String(today.getHours());
    var mm = String(today.getMinutes());
    dateTimeNow = dateTimeNow + 'T' + hh + ':' + mm;
    return dateTimeNow;
}
$(document).ready(function (){ // wird ausgeführt sobald die Seite geladen ist
    var dateNow = getDate();
    $('#newExpirationDate').attr('min', dateNow); // hierdurch kann kein vergangenes Datum ausgewählt werden
    dateNow = getDateAndTime();
    $('#timeslot_1').attr('min', dateNow);
    $("#liste").hide();
    $("#details").hide();
    $("#messages").hide();
    loadAppointments();
});

function addNewTimeslot(counter){ // sorgt dafür dass weitere Möglichkeiten für Timeslots bei der Appointmenterstellung erscheinen
    $("#addedTimeslots").append("<div class='input-group newTimeslots' id='newTimeslots'><span class='input-group-text input-group-left-example'>Timeslot " + counter + " von:</span><input class='form-control timeslot_start' type='datetime-local' onkeypress='return false' id='timeslot_" + counter + "_start'><span class='input-group-text input-group-left-example'>bis:</span><input class='form-control timeslot_end' type='datetime-local' onkeypress='return false' id='timeslot_" + counter + "_end'></div>")
    var dateTimeNow = getDateAndTime();
    $("input[type=datetime-local]").attr('min', dateTimeNow);
    counter++;
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(' + counter + ')');
}

function loadAppointments(){ // liest und schreibt alle Appointments die es in der DB gibt
    $.ajax({
        type:"GET",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "getAppointmentList", param: ""},
        dataType: "json",
        success: function(response){
            console.log(response);
            $.each(response, function (i, p){
                if(p['isExpired'] == false){ 
                    $("#listitems").append("<li class='list-group-item'><b>" + p['title'] + "</b></li>");
                }
                else{ // wenn das Appoitment abgelaufen ist, wird dieses anders dargestellt
                    $("#listitems").append("<li class='list-group-item' style='color: grey'><b>" + p['title'] + "</b></li>");
                }
                $("#listitems li:last-child").attr("data-id", p['app_id']); // die id des Appointments wird hier für später gespeichert
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

function loadDetails(){ // zeigt die Details und Votemöglichkeiten für das ausgewählte Appointment (wenn nicht abgelaufen)
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
            if(response.isExpired == true){ // Meldung für den User falls Appointment bereits abgelaufen
                $("#detailitem").append("<li class='list-group-item' style='color: red'><b>Dieses Appointment ist bereits abgelaufen!</b></li>");
            }
            $("#detailitem").append("<li class='list-group-item'><b>" + response.title + "</b></li>");
            $("#detailitem").append("<li class='list-group-item'>von <u>" + response.creator + "</u></li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.description + "</li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.location + "</li>");
            let counter = 0;
            $.each(response.timeslots, function() {
                if(response.isExpired == false){
                    $("#timeslots").append("<label class='list-group-item'><input class='form-check-input me-1' type='checkbox' checkbox-id='" + response.timeslots[counter].slot_id + "'>" + response.timeslots[counter].start_datetime + " bis " + response.timeslots[counter].end_datetime + "</label>");
                    $("#entryFields").show();
                    $("#voteBtn").show();
                }
                else{ // wenn abgelaufen, soll man nicht mehr wählen, Namen eintragen und voten können
                    $("#entryFields").hide();
                    $("#voteBtn").hide();
                    $("#timeslots").append("<label class='list-group-item me-1'>" + response.timeslots[counter].start_datetime + " bis " + response.timeslots[counter].end_datetime + "</label>");
                }
                counter++;
            });
            counter = 0;
            $("#partAndComm").append("<li class='list-group-item'><b>derzeitige Votes</b></li>");
            $.each(response.participants, function() {
                $("#partAndComm").append("<li class='list-group-item'><u>" + response.participants[counter].username + "</u> hat folgende Timeslots gewählt:</li>");
                let partSlotCounter = 0
                $.each(response.participants[counter].slot_ids, function(){
                    let slotCounter = 0;
                    $.each(response.timeslots, function(){
                        if((response.participants[counter].slot_ids[partSlotCounter]) == (response.timeslots[slotCounter].slot_id)){
                            $("#partAndComm").append("<li class='list-group-item'>" + response.timeslots[slotCounter].start_datetime + " bis " + response.timeslots[slotCounter].end_datetime + "</li>");
                        }
                        slotCounter++;
                    });
                    partSlotCounter++;
                });
                counter++;
            });
            $("#partAndComm").append("<li class='list-group-item'><b>derzeitige Kommentare</b></li>");
            counter = 0;
            $.each(response.comments, function(){ // zeigt jeden Kommentar und den zugehörigen User an
                $("#partAndComm").append("<li class='list-group-item'>" + response.comments[counter].username + ": " + response.comments[counter].message + "</li>");
                counter++;
            });
        },
        error: function(response){
            $("#entryFields").hide();
            $("#detailitem").append("<li class='list-group-item' style='color: red'><b>an error occured :(</b></li>");
        }
    });
    $("#details").show();
}

function sendNewData(){ // fürs Erstellen eines neuen Appointments, überprüft zuerst ob alle Felder ausgefüllt wurden
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
    var expiration_date = $("#newExpirationDate").val();
    $("#expirationError").remove();

    let newTimeslots = [];

    function Timeslot(start, end) {
        this.start_datetime = start;
        this.end_datetime = end;
    }

    $.each($(".newTimeslots"), () => {
        //let timeslot = new Timeslot($("#timeslot_start").val(), $("#timeslot_end").val());
        //newTimeslots.push(timeslot);
        let timeslot = {
            start_datetime: $(".timeslot_start").val(),
            end_datetime:  $(".timeslot_end").val()
        }
        newTimeslots.push(timeslot);
    });

    /*$.each($(".newTimeslots"), () => {
        //$.each($("#newTimeslots"), () => {
            //let timeslot = new Timeslot($("#timeslot_start").val(), $("#timeslot_end").val());
            //newTimeslots.push(timeslot);
            let timeslot = {
                start_datetime: $("#timeslot_start").val(),
                end_datetime:  $("#timeslot_end").val()
            }
            newTimeslots.push(timeslot);
        //});
    });*/

    console.log(newTimeslots);

    /*$("input[type=datetime-local]").each(function(){ // speichert alle Timeslots, abwechselnd als start und end Timeslot
        console.log($(this).val());
        if(counter % 2 == 0){
            //newTimeslots[counter].start_datetime = $(this).val();
        }
        else{
            //newTimeslots[counter].end_datetime = $(this).val();
            counter++;
        }
    });*/
    /*
    var newData = {title: newTitle, creator: newCreator, description: newDescription, location: newLocation, expirationDate: expiration_date, timeslots: newTimeslots}
    console.log(newData);
    newData = JSON.stringify(newData);
    $("input").val(""); // alle Eingabefelder werden geleert nachdem alle Informationen beschaffen wurden
    console.log(newData);
    $.ajax({ // schicken ans Backend für die weitere Verarbeitung und Eintragung in die DB
        type: "POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "newAppointment", param: newData},
        dataType: "json",
        success: function(response){
            $("#messages").append("<p style='color: blue'><b>das Appointment wurde angelegt :)</b></p>");
            $("#messages").show().delay(5000).fadeOut().empty();
        },
        error: function(){
            $("#messages").append("<p style='color: red'><b>Ein Fehler ist aufgetreten :(</b></p>");
            $("#messages").show().delay(5000).fadeOut().empty();
        }
    });*/
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(2)');
    $("#detailitem").empty();
    $("#timeslots").empty();
    $("#partAndComm").empty();
    $("#details").hide();
    $("#addedTimeslots").empty();
    $("#listitems").empty();
    loadAppointments(); // damit ist das neue Appointment in der Liste einsehbar
}

function addVotes(){ // fürs Voten und Kommentare abgeben, überprüft ob ein Name eingegeben wurde, Kommentar optional
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
    //var app_id = 
    var newVotes = {app_id: app_id, slot_ids: votesArray, username: partName, comment: partComm}
    newVotes = JSON.stringify(newVotes);
    $("input").val("");
    console.log(newVotes);
    $.ajax({ // die Informationen werden ans Backend geschickt und dort verarbeitet
        type:"POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "addVotes", param: newVotes},
        dataType: "json",
        success: function(response){

        },
        error: function(){
            $("#messages").append("<p style='color: red'><b>Ein Fehler ist aufgetreten :(</b></p>");
            $("#messages").show().delay(5000).fadeOut().empty();
        },
    });
    $("#detailitem").empty();
    $("#timeslots").empty();
    $("#partAndComm").empty();
    $("#nameError").remove();
    $("#details").hide();
}