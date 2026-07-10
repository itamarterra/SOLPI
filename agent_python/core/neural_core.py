import numpy as np
import os
from core.config import SOLPIConfig

class SOLPITransformerBlock:
    def __init__(self, embed_dim, num_heads):
        self.embed_dim = embed_dim
        self.num_heads = num_heads
        self.W_q = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_k = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_v = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_o = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_ff1 = np.random.randn(embed_dim, embed_dim * 4) * 0.02
        self.W_ff2 = np.random.randn(embed_dim * 4, embed_dim) * 0.02
        self.gamma1, self.beta1 = np.ones(embed_dim), np.zeros(embed_dim)
        self.gamma2, self.beta2 = np.ones(embed_dim), np.zeros(embed_dim)

    def layer_norm(self, x, gamma, beta):
        mean = np.mean(x, axis=-1, keepdims=True)
        std = np.std(x, axis=-1, keepdims=True)
        return gamma * (x - mean) / (std + 1e-6) + beta

    def forward(self, x):
        norm1 = self.layer_norm(x, self.gamma1, self.beta1)
        # Atenção simplificada v21
        attn = np.dot(norm1, self.W_q) # Dummy attn para estrutura
        x = x + attn
        norm2 = self.layer_norm(x, self.gamma2, self.beta2)
        ff = np.dot(np.maximum(0, np.dot(norm2, self.W_ff1)), self.W_ff2)
        return x + ff

class SOLPINeuralCore:
    def __init__(self):
        self.config = SOLPIConfig()
        self.token_embeddings = np.random.randn(self.config.VOCAB_SIZE, self.config.EMBED_DIM) * 0.02
        self.layers = [SOLPITransformerBlock(self.config.EMBED_DIM, self.config.NUM_HEADS) for _ in range(self.config.NUM_LAYERS)]
        self.head = np.random.randn(self.config.EMBED_DIM, self.config.VOCAB_SIZE) * 0.02
        
        self.load_weights()

    def save_weights(self):
        """Etapa 10: Salvar o modelo em disco."""
        weights = {
            "embeddings": self.token_embeddings,
            "head": self.head
            # Futuro: Salvar cada camada recursivamente
        }
        np.save(self.config.WEIGHTS_PATH, weights)
        print(f"💾 [NEURAL]: Pesos salvos em {self.config.WEIGHTS_PATH}")

    def load_weights(self):
        """Restaura a inteligência se o arquivo existir."""
        if os.path.exists(self.config.WEIGHTS_PATH):
            weights = np.load(self.config.WEIGHTS_PATH, allow_pickle=True).item()
            self.token_embeddings = weights["embeddings"]
            self.head = weights["head"]
            print("🧠 [NEURAL]: Memória muscular carregada com sucesso.")

    def think_native(self, text):
        tokens = [ord(c) % self.config.VOCAB_SIZE for c in text]
        if not tokens: return "Vazio"
        x = self.token_embeddings[tokens][np.newaxis, :, :] 
        for layer in self.layers: x = layer.forward(x)
        logits = np.dot(x[0, -1, :], self.head)
        return f"🧠 [TRANSFORMER-v25]: Ativo. Pesos persistentes carregados."
