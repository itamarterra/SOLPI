import os
import requests
from bs4 import BeautifulSoup

class KnowledgeEngine:
    """
    MOTOR DE INGESTÃO DE CONHECIMENTO v1.0
    Transforma arquivos e sites em inteligência local.
    """
    def __init__(self, knowledge_dir="knowledge"):
        self.knowledge_dir = knowledge_dir
        os.makedirs(self.knowledge_dir, exist_ok=True)

    def download_site(self, url, name):
        """Baixa o conteúdo de um site e salva como inteligência."""
        print(f"📥 [KNOWLEDGE]: Baixando conteúdo de {url}...")
        try:
            res = requests.get(url, timeout=15, headers={'User-Agent': 'SOLPI-Bot'})
            soup = BeautifulSoup(res.text, 'html.parser')
            
            # Remove lixo (scripts, styles)
            for script in soup(["script", "style"]):
                script.decompose()
            
            text = soup.get_text(separator='\n')
            # Limpa linhas vazias
            clean_text = "\n".join([line.strip() for line in text.splitlines() if line.strip()])
            
            file_path = os.path.join(self.knowledge_dir, f"{name}.txt")
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(f"FONTE: {url}\n\n" + clean_text)
            
            return f"✅ Conhecimento '{name}' absorvido com sucesso!"
        except Exception as e:
            return f"❌ Falha ao baixar conteúdo: {str(e)}"

    def get_local_intelligence(self, query):
        """Busca palavras-chave nos arquivos baixados."""
        relevant_chunks = []
        files = [f for f in os.listdir(self.knowledge_dir) if f.endswith(".txt")]
        
        for file in files:
            with open(os.path.join(self.knowledge_dir, file), "r", encoding="utf-8") as f:
                content = f.read()
                if query.lower() in content.lower():
                    # Pega um pedaço do texto ao redor do termo
                    relevant_chunks.append(f"📚 Do arquivo {file}:\n{content[:500]}...")
        
        return relevant_chunks
