import numpy as np

class SOLPIRMSNorm:
    """
    PACOTE 0501: RMSNorm v1.0
    Root Mean Square Layer Normalization (Padrão Llama/Gemma).
    Estabiliza o fluxo de tensores sem a necessidade de subtrair a média.
    """
    def __init__(self, dim, eps=1e-6):
        self.eps = eps
        self.weight = np.ones(dim)

    def _norm(self, x):
        return x * (np.mean(x**2, axis=-1, keepdims=True) + self.eps)**-0.5

    def forward(self, x):
        return self._norm(x) * self.weight
