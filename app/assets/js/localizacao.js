async function buscarCEP(cep) {
  cep = cep.replace(/\D/g, ''); // remove caracteres não numéricos

  if (cep.length !== 8) {
    alert("CEP inválido!");
    return;
  }

  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const data = await response.json();

    if (data.erro) {
      alert("CEP não encontrado!");
      return;
    }

    // Preenche os campos com os dados da API
    document.getElementById("endereco").value = data.logradouro || "";
    document.getElementById("cidade").value = data.localidade || "";
    document.getElementById("estado").value = data.uf || "";

  } catch (error) {
    console.error("Erro ao buscar CEP:", error);
    alert("Erro ao consultar o CEP!");
  }
}