import numpy as np
from core.tokenizer import SOLPITokenizer
from core.attention import SOLPISelfAttention
from core.layer_norm import SOLPIRMSNorm
from core.feed_forward import SOLPIFeedForward
from core.config import SOLPIConfig

class SOLPIExpertLayer:
    """PACOTE 0103: Mixture of Experts (MoE) Specialist"""
    def __init__(self, embed_dim):
        self.experts = [SOLPIFeedForward(embed_dim, embed_dim * 4) for _ in range(4)] # 4 Especialistas
        self.router = np.random.randn(embed_dim, 4) # Roteador Neural

    def forward(self, x):
        # Roteamento: decide qual especialista melhor atende este token
        router_logits = np.dot(x, self.router)
        expert_idx = np.argmax(router_logits, axis=-1)
        
        # Executa apenas o especialista escolhido (Sparse MoE)
        out = np.zeros_like(x)
        for i in range(4):
            mask = (expert_idx == i)
            if np.any(mask):
                out[mask] = self.experts[i].forward(x[mask])
        return out

class SOLPINeuralCore:
    def __init__(self):
        self.config = SOLPIConfig()
        self.tokenizer = SOLPITokenizer()
        self.norm = SOLPIRMSNorm(self.config.EMBED_DIM)
        self.attention = SOLPISelfAttention(self.config.EMBED_DIM)
        self.moe = SOLPIExpertLayer(self.config.EMBED_DIM) # Mixture of Experts
        
        self.embeddings = np.random.randn(self.config.VOCAB_SIZE, self.config.EMBED_DIM) * 0.01

    def think_native(self, text):
        tokens = self.tokenizer.encode(text)
        if not tokens: return "Vazio"
        
        x = self.embeddings[tokens]
        x = self.norm.forward(x)
        
        # Fluxo MoE: Atenção -> Roteamento -> Especialista
        attn_out = self.attention.forward(x)
        x = x + attn_out
        
        x = x + self.moe.forward(x)
        
        return f"🧠 [MOE-CORE v29]: Processado via Roteamento de Especialistas Neurais."
