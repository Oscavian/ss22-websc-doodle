function getDate(){ // findet das aktuelle Datum heraus
    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let yyyy = today.getFullYear();
    let dateNow = yyyy + '-' + mm + '-' + dd;
    return dateNow;
}
function getDateAndTime(){ // findet zusätzlich die aktuelle Zeit heraus
    let dateTimeNow = getDate();
    let today = new Date();
    let hh = String(today.getHours());
    let mm = String(today.getMinutes());
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
    $("#addedTimeslots").append("<div class='input-group newTimeslots' id='newTimeslots_" + counter + "'><span class='input-group-text input-group-left-example'>Timeslot " + counter + " von:</span><input class='form-control timeslot_start' type='datetime-local' onkeypress='return false' id='timeslot_" + counter + "_start'><span class='input-group-text input-group-left-example'>bis:</span><input class='form-control timeslot_end' type='datetime-local' onkeypress='return false' id='timeslot_" + counter + "_end'></div>")
    let dateTimeNow = getDateAndTime();
    $("input[type=datetime-local]").attr('min', dateTimeNow);
    counter++;
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(' + counter + ')');
}

function loadAppointments(){ // liest und schreibt alle Appointments die es in der DB gibt
    $("#listitems").empty();
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
    $("#nameError").remove();
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
            $("#detailitem").append("<li class='list-group-item' app-id='" + response.app_id + "' id='titleAndAppId'><b>" + response.title + "</b></li>");
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
            if(!(response.participants.length === 0)){
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
            }
            else{
                $("#partAndComm").append("<li class='list-group-item'><b>noch keine Votes, sei der Erste</b></li>");
            }
            if(!(response.comments.length === 0)){
                $("#partAndComm").append("<li class='list-group-item'><b>derzeitige Kommentare</b></li>");
                    counter = 0;
                    $.each(response.comments, function(){ // zeigt jeden Kommentar und den zugehörigen User an
                        $("#partAndComm").append("<li class='list-group-item'>" + response.comments[counter].username + ": " + response.comments[counter].message + "</li>");
                        counter++;
                    });
            }
            else{
                $("#partAndComm").append("<li class='list-group-item'><b>noch keine Kommentare, gib als erster deine Meinung ab</b></li>");
            }
        },
        error: function(response){
            $("#entryFields").hide();
            $("#detailitem").append("<li class='list-group-item' style='color: red'><b>an error occured :(</b></li>");
        }
    });
    $("#details").show();
}

function sendNewData(){ // fürs Erstellen eines neuen Appointments, überprüft zuerst ob alle Felder ausgefüllt wurden
    $("#detailitem").empty();
    $("#partAndComm").empty();
    $("#timeslots").empty();
    $("#details").hide();
    $("#titleError").remove();
    $("#creatorError").remove();
    $("#descriptionError").remove();
    $("#locationError").remove();
    $("#expirationError").remove();
    let error = 0;
    if(!$("#newTitle").val()){
        $("#titleCard").after("<div id='titleError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Titel eingeben um das Appointment zu erstellen' readonly></div>");
        error = 1;
    }
    if(!$("#newCreator").val()){
        $("#creatorCard").after("<div id='creatorError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Namen eingeben um das Appointment zu erstellen' readonly></div>");
        error = 1;
    }
    if(!$("#newDescription").val()){
        $("#descriptionCard").after("<div id='descriptionError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen eine Beschreibung eingeben um das Appointment zu erstellen' readonly></div>");
        error = 1;
    }
    if(!$("#newLocation").val()){
        $("#locationCard").after("<div id='locationError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Ort eingeben um das Appointment zu erstellen' readonly></div>");
        error = 1;
    }
    if(!$("#newExpirationDate").val()){
        $("#expirationCard").after("<div id='expirationError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen ein Ablaufdatum eingeben um das Appointment zu erstellen' readonly></div>");
        error = 1;
    }
    if(error === 1){ // egal wie viele und welche Error auftreten, gehts nicht weiter
        return;
    }
    var newTitle = $("#newTitle").val();
    var newCreator = $("#newCreator").val();
    var newDescription = $("#newDescription").val();
    var newLocation = $("#newLocation").val();
    var expiration_date = $("#newExpirationDate").val();
    var newTimeslots = [];
    function Timeslot(start, end) {
        this.start_datetime = start;
        this.end_datetime = end;
    }
    let counter = 0;
    $("input[type=datetime-local]").each(function(){
        counter++;
    });
    counter = counter/2;
    for(let i = 1; i <= counter; i++){
        let timeslot = new Timeslot($("#timeslot_" + i + "_start").val(), $("#timeslot_" + i + "_end").val());
        newTimeslots.push(timeslot);
    };

    let newData = {method: "newAppointment", title: newTitle, creator: newCreator, description: newDescription, location: newLocation, expiration_date: expiration_date, timeslots: newTimeslots}
    newData = JSON.stringify(newData);
    $("input").val(""); // alle Eingabefelder werden geleert, nachdem alle Informationen beschaffen wurden
    $("#addedTimeslots").empty();
    console.log(newData);
    $.ajax({ // schicken ans Backend für die weitere Verarbeitung und Eintragung in die DB
        type: "POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: newData,
        dataType: "json",
        success: function(response){
            $("#messages").append("<p style='color: blue'><b>das Appointment wurde angelegt :)</b></p>");
            $("#messages").show().delay(5000).fadeOut().empty();
        },
        error: function(){
            $("#messages").append("<p style='color: red'><b>Ein Fehler ist aufgetreten :(</b></p>");
            $("#messages").show().delay(5000).fadeOut();
        }
    });
    $("#btnAddTimeslot").attr('onclick', 'addNewTimeslot(2)');
    loadAppointments(); // damit ist das neue Appointment in der Liste einsehbar
}

function addVotes(){ // fürs Voten und Kommentare abgeben, überprüft ob ein Name eingegeben wurde, Kommentar optional
    if(!$("#partName").val()){
        $("#nameError").remove();
        $("#name").after("<div id='nameError' class='input-group'><input class='form-control' type='text' id='nameError' style='color: red' value='Sie müssen einen Namen eingeben um abzustimmen' readonly></div>")
        return;
    }
    let partName = $("#partName").val();
    let partComm = "";
    if($("#partComm").val()){
        partComm = $("#partComm").val();
    }
    let votesArray = [];
    $('input[type=checkbox]').each(function(){
        if($(this).is(':checked')){
            let id_number = parseInt($(this).attr('checkbox-id'));
            votesArray.push(id_number);
        }
    });
    let newApp_id = parseInt($("#titleAndAppId").attr('app-id'));
    $("input").val("");
    let newVotes = {method: "addVotes", app_id: newApp_id, slot_ids: votesArray, username: partName, comment: partComm}
    newVotes = JSON.stringify(newVotes);
    console.log(newVotes);
    $.ajax({ // die Informationen werden ans Backend geschickt und dort verarbeitet
        type:"POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: newVotes,
        dataType: "json",
        success: function(){
            $("#messages").append("<p style='color: blue'><b>Erfolgreich gevotet :)</b></p>");
            $("#messages").show().delay(5000).fadeOut().empty();
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