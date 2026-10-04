(function(){
  var f=document.getElementById('demande'), m=document.getElementById('form-msg');
  if(!f) return;

  // Pré-remplit le produit quand on arrive depuis "Demander la documentation"
  var q=new URLSearchParams(location.search), doc=q.get('doc');
  if(doc){
    f.produit.value=doc;
    f.objet.value='Documentation / dossier technique';
  }
  // Retour du formulaire sans JavaScript
  if(q.get('envoye')) m.textContent='Merci, votre demande a bien été envoyée. Nous vous répondrons rapidement.';
  if(q.get('erreur')) m.textContent='L’envoi a échoué. Écrivez-nous directement à arcomedic@wanadoo.fr.';

  if(f.t) f.t.value=Date.now();

  f.addEventListener('submit',function(e){
    e.preventDefault();
    var miss=['nom','etab','email'].filter(function(k){return !f[k].value.trim();});
    if(miss.length){
      m.textContent='Indiquez votre nom, votre établissement et votre e-mail pour recevoir une réponse.';
      f[miss[0]].focus();
      return;
    }
    var btn=f.querySelector('button[type="submit"]');
    btn.disabled=true;
    m.textContent='Envoi en cours…';
    fetch(f.action,{method:'POST',body:new FormData(f),headers:{'Accept':'application/json'}})
      .then(function(r){return r.json();})
      .then(function(d){
        m.textContent=d.message;
        if(d.ok){ f.reset(); if(f.t) f.t.value=Date.now(); }
      })
      .catch(function(){
        m.textContent='L’envoi a échoué. Écrivez-nous directement à arcomedic@wanadoo.fr.';
      })
      .then(function(){ btn.disabled=false; });
  });
})();

// Vignettes : un produit avec plusieurs photos
document.querySelectorAll('.thumbs button').forEach(function(b){
  b.addEventListener('click',function(){
    var card=b.closest('.product'), main=card.querySelector('.ph img');
    main.src=b.dataset.src; main.alt=b.dataset.alt;
    card.querySelectorAll('.thumbs button').forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false');});
  });
});
