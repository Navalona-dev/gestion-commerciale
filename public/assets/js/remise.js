function addRemiseProduit(idProduit, uri, idAffaire = null) {

  let displayConfirmation = false;
  let elementAfter = null
  try {
    //displayConfirmation = ($("td.remise_"+ idProduit +" a").text().trim() != "%");
    displayConfirmation = $("td.remise_"+ idProduit +" a").children().hasClass("bi");
   
  } catch (e) {
    console.log(e); // Logs the error
  }

  if (!displayConfirmation) {
    
    let messageAlert = "Les remises ne peuvent pas être modifiées une fois créées. Si vous devez ajuster le montant ou le pourcentage d'une remise, veuillez la supprimer et en créer une nouvelle avec les paramètres corrects."
    
    if (!confirm(messageAlert)) {
      return false;
    }

  }

  $(".loadBody").css("display", "block");
  $.ajax({
    type: "post",
    url: "/admin/affaires/financiere/addRemise/ajax",
    data: { idProduit: idProduit, type: "produit", uri: uri, idAffaire: idAffaire },
    success: function (response) {
      $("#modalRemiseEmpty").empty();
      $("#modalRemiseEmpty").append(response);
      $("#modalRemise_produit").modal("show");
      $(".loadBody").css("display", "none");

      return false;
    },
  });

  return false;
}

function addRemiseAffaire(idAffaire, uri, option) {
  var applicationID = $("input[name='applicationID']").val();
  $(".loadBody").css("display", "block");
  $.ajax({
    type: "post",
    url: "/admin/affaires/financiere/addRemise/ajax",
    data: { idAffaire: idAffaire, type: "affaire", uri: uri, option: option, applicationId: applicationID },
    success: function (response) {
      $("#blocModalReglement").empty();
      $("#blocModalReglement").append(response);
      $("#modalRemise_affaire").modal("show");
      $(".loadBody").css("display", "none");

      return false;
    },
  });
  return false;
}

function changeTypeRemise(elt) {
  $(elt).attr("checked", "checked");

  var val = $(elt).val();

  if (val === "1") {
    $(".typeRemiseMontant").empty();
    $(".typeRemiseMontant").append("%");
  } else {
    $(".typeRemiseMontant").empty();
    $(".typeRemiseMontant").append("Ar");
  }

  var montant = $("input[name='montantRemise']").val();

  if (montant === "") {
  } else {
    changeMontantRemise(montant);
  }
}

function changeMontantRemise(montant, isFrais = false) {
  var typeRemise = $("input[name='typeremise']:checked").val();

  var montantInitial = parseFloat($("input[name='montantInitial']").val());

  var nouveauMontant = "";

  //montantInitial = montantInitial.toFixed(2);

  if (montant === "") {
    alert("Merci de saisir un montant !!");
    $(".newMontant").empty();
    return false;
  }

  if (typeRemise === "2") {
    /*if (montant > montantInitial) {
            $("input[name='montantRemise']").val("");
            alert("Le montant a depassé le montant initial. Merci de saisir à nouveau ");
            $(".newMontant").empty();
            return false;

        }*/
    if (!isFrais) {
      nouveauMontant = parseFloat(montantInitial) - parseFloat(montant);
    } else {
      nouveauMontant = parseFloat(montantInitial) + parseFloat(montant);
    }
    

    $("#montantRemiseFinale").val(montant);
  } else {
    var montantRemise = (montantInitial * montant) / 100;

    //montantRemise = montantRemise.toFixed(2);

    /*if (montantRemise > montantInitial) {
            $("input[name='montantRemise']").val("");
            alert("Le montant a depassé le montant initial. Merci de saisir à nouveau ");
            $(".newMontant").empty();
            return false;

        }*/
    
    if (!isFrais) {
      nouveauMontant = parseFloat(montantInitial) - parseFloat(montantRemise);
      $("#montantRemiseFinale").val(montantRemise);

      $(".typeRemiseMontant").empty();
      $(".typeRemiseMontant").append("% (- " + montantRemise + "Ar)");
    } else {
      nouveauMontant = parseFloat(montantInitial) + parseFloat(montantRemise);
      $("#montantRemiseFinale").val(montantRemise);

      $(".typeRemiseMontant").empty();
      $(".typeRemiseMontant").append("% (" + montantRemise + "Ar)");
    }
    
    
  }
  console.log("nouveauMontant", nouveauMontant, typeRemise);
  $(".newMontant").empty();

  $(".newMontant").append(nouveauMontant.toFixed(2) + "Ar");
}

function saveRemise(type, id, isFrais = false) {
  $(".loadBody").css("display", "block");

  var formData = new FormData($("#formRemise")[0]);

  var applicationID = $("input[name='applicationID']").val();
  formData.append("id", id);
  formData.append("applicationId", applicationID);
  showSpinner();
  $.ajax({
    type: "POST",
    url: $("#formRemise").attr("action"),
    data: formData,
    processData: false,
    contentType: false,
    error: function (jqXHR, textStatus, errorMessage) {
      console.log(errorMessage); // Optional
    },
    success: function (response) {
      //reloadTabFinanciere(response);

      /*if (!isFrais) {
        $("#modalRemise_" + type).modal("hide");
      } else {
        $("#modalFraisTechnique_" + type).modal("hide");
      }*/
      $("#modalRemise_" + type).modal("hide");
      $("#financiereProduct").empty();
      $("#financiereProduct").replaceWith(response);
      hideSpinner();

      //$(".loadBody").css("display", "none");

      return false;
    },
  });

  return false;
}

function deleteRemiseProduitAffaire(type, id, isFrais = false) {
  //$(".loadBody").css("display", "block");
  if (confirm('Voulez-vous vraisment supprimer cette remise?')) {
    var formData = new FormData($("#deleteRemise")[0]);

    formData.append("idAffaire", id);
    showSpinner();
    $.ajax({
      type: "POST",
      url: $("#deleteRemise").attr("action"),
      data: formData,
      processData: false,
      contentType: false,
      error: function (jqXHR, textStatus, errorMessage) {
        console.log(errorMessage); // Optional
      },
      success: function (response) {
        //reloadTabFinanciere(response);
        $("#modalRemise_" + type).modal("hide");
        $("#financiereProduct").empty();
        $("#financiereProduct").replaceWith(response);
        hideSpinner();
        //$(".loadBody").css("display", "none");

        return false;
      },
    });
  }
  

  return false;
}


function addFraistechniqueAffaire(idAffaire, uri, option, isFrais = true) {
  var applicationID = $("input[name='applicationID']").val();
  $(".loadBody").css("display", "block");
  $.ajax({
    type: "post",
    url: "/admin/affaires/financiere/addRemise/ajax",
    data: { idAffaire: idAffaire, type: "affaire", uri: uri, option: option, applicationId: applicationID, isFrais: isFrais },
    success: function (response) {
      $("#blocModalReglement").empty();
      $("#blocModalReglement").append(response);
      $("#modalFraisTechnique_affaire").modal("show");
      $(".loadBody").css("display", "none");

      return false;
    },
  });
  return false;
}