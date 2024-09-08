
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
// Ouvre la modale et affiche l'image
function openModal(element) {
    var modal = document.getElementById("modalSnap");
    var modalImg = document.getElementById("modalSnapImg");
    var captionText = document.getElementById("caption");

    modal.style.display = "block";
    modalImg.src = element.src;
    captionText.innerHTML = element.nextElementSibling.innerHTML;
}

// Ferme la modale en cliquant sur la croix ou en dehors de l'image
function closeModal() {
    var modal = document.getElementById("modalSnap");
    modal.style.display = "none";
}

// Fermer la modale en cliquant en dehors de l'image
window.onclick = function (event) {
    var modal = document.getElementById("modalSnap");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}


function removeImg(img) {
    $.hideAlert();
    var filepath = img.id;
    bootbox.confirm('{{Êtes-vous sûr de vouloir supprimer cette image}} : <span style="font-weight: bold ;">' + img.id + '</span> ?', function (result) {
        if (result) {
            jeedom.removeImageIcon({
                filepath: img.id,
                error: function (error) {
                    $('#div_iconSelectorAlert').showAlert({ message: error.message, level: 'danger' });
                },
                success: function (data) {
                    $('#div_iconSelectorAlert').showAlert({ message: '{{Fichier supprimé avec succès}}', level: 'success' });

                    window.location.reload();
                }
            })
        }
    })
}