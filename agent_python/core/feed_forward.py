import numpy as np

class SOLPIFeedForward:
    """
    PACOTE 10: FEED FORWARD v1.0
    Rede neural de duas camadas que processa cada token individualmente.
    Arquitetura: Linear -> ReLU -> Linear
    """
    def __init__(self, embed_dim, ff_dim):
        self.W1 = np.random.randn(embed_dim, ff_dim) * np.sqrt(2/embed_dim)
        self.b1 = np.zeros(ff_dim)
        self.W2 = np.random.randn(ff_dim, embed_dim) * np.sqrt(1/ff_dim)
        self.b2 = np.zeros(embed_dim)

    def forward(self, x):
        """
        Executa o refinamento: xW1 + b1 -> ReLU -> xW2 + b2
        """
        self.z1 = np.dot(x, self.W1) + self.b1
        self.a1 = np.maximum(0, self.z1) # ReLU
        return np.dot(self.a1, self.W2) + self.b2
